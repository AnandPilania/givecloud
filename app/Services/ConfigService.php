<?php

namespace Ds\Services;

use ArrayAccess;
use ArrayIterator;
use CachingIterator;
use Carbon\Carbon;
use Countable;
use Ds\Domain\MissionControl\MissionControlService;
use Ds\Domain\Settings\GivecloudExpressConfigRepository;
use Ds\Domain\Shared\Date;
use Ds\Domain\Shared\DateTime;
use Ds\Domain\Shared\Exceptions\MessageException;
use Ds\Illuminate\Database\MySqlSnapshot;
use Illuminate\Container\Container;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Encryption\Encrypter;
use Illuminate\Foundation\Bootstrap\SetRequestForConsole;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use IteratorAggregate;
use JsonSerializable;
use Laravel\Telescope\Storage\DatabaseEntriesRepository as TelescsopeStorage;
use Rundiz\Serializer\SerializerStatic;

class ConfigService implements ArrayAccess, Arrayable, Countable, IteratorAggregate, Jsonable, JsonSerializable
{
    /** @var \Ds\Services\ConfigService */
    protected static $instance;

    /** @var string */
    protected $accountName;

    /** @var bool */
    protected $autoSave = true;

    /** @var bool */
    protected $booted = false;

    /** @var array */
    protected $config = [];

    /** @var array */
    protected $defaults = [];

    /** @var array */
    protected $encrypted = [
        'double_the_donation_private_key',
        'dpo_api_key',
        'dpo_pass',
        'infusionsoft_token',
        'passport_personal_access_client_secret',
        'shipping_canadapost_pass',
        'shipping_fedex_pass',
        'shipping_ups_access_key',
        'shipping_ups_pass',
        'shipping_usps_pass',
        'ss_api_key', // defunct
        'ss_pass', // defunct
        'taxcloud_api_key',
        'twilio_subaccount_token',
    ];

    /** @var \Illuminate\Encryption\Encrypter */
    protected $encrypter;

    /** @var bool */
    protected $loaded = false;

    /** @var array */
    protected $stamps = [];

    /** @var array */
    protected $unsaved = [];

    /** @var \Ds\Domain\Commerce\Models\PaymentProvider */
    protected $safesave;

    /**
     * Create an instance.
     */
    public function __construct()
    {
        if (! $this->getApp()) {
            throw new MessageException('Config service requires an app');
        }

        $this->resolveAccountName();

        if ($this->inTestingEnvironment()) {
            $this->registerTestingBindings();
        }
    }

    /**
     * Resolve the account name that will be used to load
     * the corresponding site configuration.
     *
     * @return void
     */
    protected function resolveAccountName(): void
    {
        // Intentionally using superglobal to allow config service to be minimally
        // initialized prior to Laravel bootstrapping or booting
        $this->accountName = $_SERVER['HTTP_X_GIVECLOUD_DOMAIN'] ?? null;

        if (empty($this->accountName)) {
            $this->accountName = getenv('HTTP_X_GIVECLOUD_DOMAIN', true) ?: getenv('HTTP_X_GIVECLOUD_DOMAIN');
        }

        if (empty($this->accountName)) {
            throw new MessageException('Unable to resolve account name');
        }
    }

    /**
     * Register the testing bindings into the container.
     *
     * @return void
     */
    protected function registerTestingBindings(): void
    {
        $this->getApp()->bind(
            \Ds\Domain\MissionControl\MissionControlService::class,
            \Tests\Fakes\FakeMissionControlService::class
        );

        $this->getApp()->bind(
            \Ds\Domain\MissionControl\ShortlinkService::class,
            \Tests\Fakes\FakeShortlinkService::class
        );
    }

    /**
     * Check if we're operating in a testing environment.
     *
     * @return bool
     */
    public function inTestingEnvironment(): bool
    {
        return in_array($this->accountName, ['testing']);
    }

    /**
     * Setup the config service for testing.
     *
     * This is called to setup/reload the config service in between
     * tests while the test suite is running. This is neccessary because
     * the config service is required prior to Laravel being
     * started/bootstrapped. As a result it not reset with other
     * services when a new Laravel app is created in between tests
     * being run.
     *
     * @return void
     */
    public function setupForTesting(): void
    {
        // Ensure that configs will be reloaded
        $this->loaded = false;

        // Setup for testing by resetting booted state and
        // rebinding the testing bindings
        $this->booted = false;
        $this->loaded = false;
        $this->registerTestingBindings();
    }

    /**
     * Boot the config.
     */
    public function boot()
    {
        if ($this->booted) {
            return;
        }

        if ($this->inTestingEnvironment()) {
            $this->registerTestingBindings();
        }

        $site = site();

        // Set the site-specific configurations
        config([
            'cache.default' => 'site',
            'cache.stores.file.path' => storage_path('framework/cache/data') . '/' . $this->get('ds_account_name'),
            'cache.stores.site.prefix' => 'sites:' . $this->get('ds_account_name'),

            'database.default' => $site->db_connection,
            "database.connections.{$site->db_connection}.database" => $site->db_name,

            'gateways.paypal.caching.filename' => storage_path('logs') . '/' . $this->get('ds_account_name') . '_paypal_cache',
            'gateways.paypal.logging.filename' => storage_path('logs') . '/' . $this->get('ds_account_name') . '_paypal_log',

            'telescope.storage.database.connection' => $site->db_connection,
        ]);

        // Ensure testing database have been loaded when in a testing
        // environment to allow configs to be read from database
        if ($this->inTestingEnvironment()) {
            MySqlSnapshot::ensureLoaded('prototype-site');
        }

        // Set Passport personal access client ID and secret
        if ($this->get('passport_personal_access_client_secret')) {
            config([
                'passport.personal_access_client.id' => $this->get('passport_personal_access_client_id'),
                'passport.personal_access_client.secret' => $this->get('passport_personal_access_client_secret'),
            ]);
        }

        // Set the site-specific App URL require for
        // proper generation of URLs from either Artisan
        // or when handling queued items
        config(['app.url' => secure_site_url()]);

        // We need to update the request with the new host information but without
        // triggering RequestHandled event so we can use SetRequestForConsole
        if ($this->getApp()->runningInConsole()) {
            (new SetRequestForConsole)->bootstrap($this->getApp());
        }

        // Telescope is provided connection during the service provider
        // registration phase so we need to provide the updated connection
        if (class_exists(TelescsopeStorage::class)) {
            $this->getApp()->when(TelescsopeStorage::class)
                ->needs('$connection')
                ->give(config('telescope.storage.database.connection'));
        }

        $this->booted = true;
    }

    /**
     * Set auto save.
     *
     * @param bool $autoSave
     * @return $this
     */
    public function setAutoSave(bool $autoSave): ConfigService
    {
        $this->autoSave = $autoSave;

        return $this;
    }

    /**
     * Set the config.
     *
     * @param array $config
     * @return $this
     */
    public function setConfig(array $config = []): ConfigService
    {
        $this->config = $config;
        $this->loaded = true;

        return $this;
    }

    /**
     * Override specific keys.
     *
     * @param string $key
     * @return mixed
     */
    protected function checkForOverride(string $key)
    {
        if ($key === 'ds_account_name') {
            return $this->accountName;
        }

        if ($key === 'use_givecloud_express') {
            return null;
        }

        if ($this->booted && $key === 'timezone') {
            return site('timezone') ?: null;
        }

        if ($this->booted && isGivecloudExpress()) {
            $unavailableConfigs = app(GivecloudExpressConfigRepository::class)->getConfigOverrides();

            return value($unavailableConfigs[$key] ?? null);
        }
    }

    /**
     * Get a config value.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected function getConfigValue(string $key, $default = null)
    {
        if (Str::contains($key, ':')) {
            $cast = Str::before($key, ':');
            $key = Str::after($key, ':');
        } else {
            $cast = '';
        }

        $value = $this->checkForOverride($key)
            ?? $this->config[$key]
            ?? $this->defaults[$key]
            ?? $default;

        if (in_array($key, $this->encrypted)) {
            try {
                $value = $this->getEncrypter()->decrypt($value);
            } catch (DecryptException $e) {
                // do nothing
            }
        }

        // Some configuration values are translation keys.
        // In this case they are prefixed with "trans:".
        $translatePrefix = 'trans:';
        if (is_string($value) && strpos($value, $translatePrefix) === 0) {
            $value = trans(substr($value, strlen($translatePrefix)));
        }

        // detect eloquent model which require deserialization
        if (! $cast && preg_match('/^Ds\\\\Models\\\\[a-z\\\\]+#\d+$/i', $value)) {
            $cast = 'model';
        }

        $value = SerializerStatic::maybeUnserialize($value);

        switch ($cast) {
            case 'bool':
            case 'boolean':
            case 'int':
            case 'integer':
            case 'float':
            case 'double':
                return nullable_cast($cast, $value);
            case 'csv':  return str_getcsv($value);
            case 'date': return Date::parseDateTime($value);
            case 'datetime': return DateTime::parseDateTime($value);
            case 'list': return volt_explode($value, ',');
            case 'list_nl': return volt_explode($value, "\n");
            case 'json': return json_decode($value);
            case 'php':  return SerializerStatic::maybeUnserialize($value);
            case 'model':
                if (strpos($value, '#') === false) {
                    return new $value;
                }

                [$klass, $id] = explode('#', $value, 2);

                return (new $klass)->find($id);
        }

        return $value;
    }

    /**
     * Get a specific key from the config data.
     *
     * @param string|array $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        $this->lazyLoad($key);

        $values = [];
        foreach (Arr::wrap($key) as $subKey) {
            $values[] = $this->getConfigValue($subKey, $default);
        }

        return is_array($key) ? $values : current($values);
    }

    /**
     * Determine if a key exists in the config data.
     *
     * @param string|array $key
     * @return bool
     */
    public function has($key): bool
    {
        $this->lazyLoad($key);

        foreach (Arr::wrap($key) as $subKey) {
            $value = $this->checkForOverride($subKey)
                ?? $this->config[$subKey]
                ?? $this->defaults[$subKey]
                ?? null;

            if ($value === null || $value === '') {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the modified time for a key in the config data.
     *
     * @param string $key
     * @return \Carbon\Carbon
     */
    public function modified(string $key): Carbon
    {
        $this->lazyLoad($key);

        if ($this->checkForOverride($key) === null && isset($this->stamps[$key])) {
            return fromUtc($this->stamps[$key]);
        }

        return fromUtc('now');
    }

    /**
     * Set a specific key to a value in the config data.
     *
     * @param string|array $key
     * @param mixed $value
     * @return bool
     */
    public function set($key, $value = null): bool
    {
        if (is_string($key)) {
            $key = [$key => $value];
        }

        foreach ($key as $subKey => $value) {
            if (Str::contains($subKey, ':')) {
                $serialize = Str::before($subKey, ':');
                $subKey = Str::after($subKey, ':');
            } else {
                $serialize = '';
            }

            switch ($serialize) {
                case 'csv':  $value = str_putcsv($value); break;
                case 'list': $value = implode(',', $value); break;
                case 'list_nl': $value = implode("\n", $value); break;
                case 'json': $value = json_encode($value); break;
                case 'php':  $value = SerializerStatic::maybeSerialize($value); break;
            }

            // To maintain backwards compatibility with previous versions
            // of the settings file arrays need to be converted to CSV, booleans
            // converted to '1' or '0'
            if (is_array($value)) {
                $value = implode(',', $value);
            } elseif (is_bool($value)) {
                $value = $value ? '1' : '0';
            } elseif (is_object($value)) {
                if ($value instanceof \Illuminate\Database\Eloquent\Model) {
                    $value = get_class($value) . ($value->exists ? '#' . $value->getKey() : '');
                } else {
                    $value = serialize($value);
                }
            } else {
                $value = nullable_cast('string', $value);
            }

            if (Arr::get($this->defaults, $subKey) === $value) {
                $value = null;
            }

            $currentValue = Arr::get($this->config, $subKey);

            if (in_array($subKey, $this->encrypted)) {
                try {
                    $currentValue = $this->getEncrypter()->decrypt($currentValue);
                } catch (DecryptException $e) {
                    // There's a chance that the "current" value may not be encrypted. However that
                    // should only occur incases where a config that was previously not encrypted
                    // was updated to be encrypted.
                }

                if ($value !== $currentValue) {
                    $this->unsaved[$subKey] = ($value === null) ? null : $this->getEncrypter()->encrypt($value);
                }

                continue;
            }

            if ($value !== $currentValue) {
                $this->unsaved[$subKey] = $value;
            }
        }

        $this->config = array_merge($this->config, $this->unsaved);

        if ($this->autoSave) {
            return $this->save();
        }

        return false;
    }

    /**
     * Set known keys in the config data.
     *
     * @param array $data
     * @return bool
     */
    public function setKnown(array $data): bool
    {
        $known = array_keys(config('sys.defaults'));

        $keys = array_keys($data);
        $keys = array_values(array_intersect($known, $keys));

        return $this->set(Arr::only($data, $keys));
    }

    /**
     * Set a specific key to a value in the config data using values
     * from the request input.
     *
     * @param \Illuminate\Http\Request $request
     */
    public function setFromRequest(Request $request = null): bool
    {
        if (empty($request)) {
            $request = request();
        }

        return $this->setKnown($request->post());
    }

    /**
     * Unset a key in the config data.
     *
     * @param string|array $key
     */
    public function forget($key)
    {
        $this->set(array_fill_keys(Arr::wrap($key), null));
    }

    /**
     * Get all config data.
     *
     * @return array
     */
    public function all(): array
    {
        $this->load();

        $this->checkEncryption();

        return array_merge($this->defaults, $this->config);
    }

    /**
     * Get config data.
     *
     * @return array
     */
    public function toArray(): array
    {
        $this->load();

        $this->checkEncryption();

        return $this->config;
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Get the collection of items as JSON.
     *
     * @param int $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * Count the number of items in the config.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->toArray());
    }

    /**
     * Get an iterator for the config.
     *
     * @return \ArrayIterator
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->toArray());
    }

    /**
     * Get a CachingIterator instance.
     *
     * @param int $flags
     * @return \CachingIterator
     */
    public function getCachingIterator($flags = CachingIterator::CALL_TOSTRING)
    {
        return new CachingIterator($this->getIterator(), $flags);
    }

    /**
     * Determine if an item exists at an offset.
     *
     * @param mixed $key
     * @return bool
     */
    public function offsetExists($key): bool
    {
        return $this->has($key);
    }

    /**
     * Get an item at a given offset.
     *
     * @param mixed $key
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    /**
     * Set the item at a given offset.
     *
     * @param string|null $key
     * @param mixed $value
     */
    public function offsetSet($key, $value): void
    {
        if (is_null($key)) {
            throw new MessageException('Configs must be keyed');
        }

        $this->set($key, $value);
    }

    /**
     * Unset the item at a given offset.
     *
     * @param string $key
     */
    public function offsetUnset($key): void
    {
        $this->forget($key);
    }

    /**
     * Make sure data is encrypted.
     */
    protected function checkEncryption()
    {
        foreach ($this->encrypted as $key) {
            if (Arr::has($this->config, $key)) {
                try {
                    $this->getEncrypter()->decrypt($this->config[$key]);
                } catch (DecryptException $e) {
                    $this->config[$key] = $this->getEncrypter()->encrypt($this->config[$key]);
                }
            }
        }
    }

    /**
     * Load config from database.
     */
    protected function loadFromDatabase()
    {
        $databaseName = site('db_name');

        $data = DB::table("$databaseName.configs as c1")
            ->select([
                'c1.config_key',
                DB::raw('any_value(c1.config_value) as config_value'),
                DB::raw('any_value(c1.created_at) as created_at'),
            ])->leftJoin("$databaseName.configs as c2", function ($join) {
                $join->on('c2.config_key', '=', 'c1.config_key');
                $join->whereRaw('c1.created_at < c2.created_at');
            })->whereNull('c2.config_key')
            ->groupBy('c1.config_key')
            ->orderByRaw('NULL')
            ->get();

        $this->config = $data->pluck('config_value', 'config_key')->all();
        $this->stamps = $data->pluck('created_at', 'config_key')->all();
    }

    /**
     * Make sure data is loaded.
     *
     * @param bool $force
     */
    public function load($force = false)
    {
        if (! $force && $this->loaded) {
            return;
        }

        $this->defaults = config('sys.defaults');

        if (! defined('APP_LEVEL_ENABLED')) {
            $this->loadFromDatabase();
        }

        $this->loaded = true;
    }

    /**
     * Lazy loading of config data. Allows deferral of loading
     * config data if keys are all satisfiable via overrides.
     *
     * @param string|array $key
     */
    protected function lazyLoad($key)
    {
        foreach (Arr::wrap($key) as $subKey) {
            if ($this->checkForOverride($subKey) === null) {
                $this->load();
                break;
            }
        }
    }

    /**
     * Save any changes done to the config data.
     */
    public function save(): bool
    {
        if (empty($this->unsaved)) {
            return true;
        }

        $configs = [];
        foreach ($this->unsaved as $key => $value) {
            $configs[] = [
                'config_key' => $key,
                'config_value' => $value,
                'created_at' => fromUtc('now', 'datetime'),
                'created_by' => user('id') ?? 1,
            ];

            if ($key === 'timezone') {
                app(MissionControlService::class)->setTimezone($value ?: $this->defaults[$key]);
            }
        }

        $result = DB::table('configs')->insert($configs);

        if ($result) {
            $this->unsaved = [];
        }

        return (bool) $result;
    }

    /**
     * Get instance.
     *
     * @return \Ds\Services\ConfigService
     */
    public static function getInstance(): ConfigService
    {
        if (empty(static::$instance)) {
            static::$instance = new static;
        }

        return static::$instance;
    }

    /**
     * Get app/container instance.
     *
     * @return \Illuminate\Contracts\Foundation\Application
     */
    protected function getApp()
    {
        // Using `Container::getInstance` is a deliberate choice since the App facade
        // requires the that the app be in a at least partially booted state.
        return Container::getInstance();
    }

    /**
     * Get an Encryptor instance.
     *
     * @return \Illuminate\Encryption\Encrypter
     */
    protected function getEncrypter(): Encrypter
    {
        if (empty($this->encrypter)) {
            if (! $this->getApp()->hasBeenBootstrapped()) {
                throw new MessageException('Encrypted configs can only be accessed after the app has bootstrapped');
            }

            // Creating our own Encrypter instance allow access to
            // encrypted configs prior to the app being booted
            $this->encrypter = new Encrypter(config('app.key'), config('app.cipher'));
        }

        return $this->encrypter;
    }
}
