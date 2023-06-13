<?php

namespace Ds\Domain\Commerce;

use Ds\Domain\Shared\Date;
use Ds\Domain\Theming\Liquid\Drops\CurrencyDrop;
use Ds\Domain\Theming\Liquid\Liquidable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;
use JsonSerializable;
use Money\Currency as PhpCurrency;
use Swap\Laravel\Facades\Swap;
use Throwable;

/**
 * @property bool $active
 * @property bool $local
 * @property bool $default
 * @property string $name
 * @property string $code
 * @property float $rate
 * @property bool $has_unique_symbol
 * @property string $symbol
 * @property string $unique_symbol
 * @property array $countries
 */
class Currency implements Arrayable, Jsonable, JsonSerializable, Liquidable
{
    /** @var array */
    protected $attributeMap = [
        'active' => 'isLocal',
        'code' => 'getCode',
        'iso_code' => 'getCode',
        'countries' => 'getCountries',
        'default_country' => 'getDefaultCountry',
        'default' => 'isDefault',
        'has_unique_symbol' => 'hasUniqueSymbol',
        'local' => 'isLocal',
        'name' => 'getName',
        'rate' => 'getRate',
        'symbol' => 'getSymbol',
        'unique_symbol' => 'getUniqueSymbol',
    ];

    /** @var array */
    protected $data;

    /**
     * Create an instance.
     *
     * @param mixed $value
     */
    public function __construct($value = null)
    {
        $currencies = static::getCurrencies();

        if ($value instanceof Currency || $value instanceof PhpCurrency) {
            $code = $value->getCode();
        } elseif ($value instanceof CurrencyDrop) {
            $code = strtoupper($value->iso_code);
        } elseif ($value) {
            $code = strtoupper($value);
        } else {
            $code = static::getDefaultCurrencyCode();
        }

        $this->data = Arr::get($currencies, $code);

        if (empty($this->data)) {
            throw new InvalidArgumentException(sprintf('[%s] is not a supported currency.', $value));
        }
    }

    /**
     * Get the exchange rate between two currencies.
     *
     * @param mixed $from
     * @param mixed $to
     * @param string|null $date
     * @return float
     */
    public static function getExchangeRate($from, $to, $date = null)
    {
        $to = new Currency($to);
        $from = new Currency($from);

        if ($to->code === $from->code) {
            return 1.0;
        }

        $key = "{$from->code}/{$to->code}";

        if (empty($date)) {
            return (float) Cache::store('app')->remember("currency:$key", now()->addHours(12), function () use ($key) {
                return Swap::latest($key)->getValue();
            });
        }

        $date = Date::parseDateTime($date);

        if (empty($date)) {
            throw new InvalidArgumentException('Unrecognized date');
        }

        $cacheKey = 'historical-currency:' . $date->format('Ymd') . ":$key";

        return (float) Cache::store('app')->rememberForever($cacheKey, function () use ($key, $date) {
            return Swap::historical($key, $date)->getValue();
        });
    }

    /**
     * Get the best currency for an IP address.
     *
     * @param string $ipAddress
     * @return \Ds\Domain\Commerce\Currency
     */
    public static function getBestLocalCurrencyForIp($ipAddress = null): Currency
    {
        try {
            $ipAddress = app('geoip')->getLocationData($ipAddress);
        } catch (Throwable $e) {
            return new Currency;
        }

        foreach (static::getLocalCurrencies() as $currency) {
            if (in_array($ipAddress->iso_code, $currency->countries)) {
                return $currency;
            }
        }

        return new Currency;
    }

    /**
     * Get the default currency code.
     *
     * @return string
     */
    public static function getDefaultCurrencyCode()
    {
        return sys_get('dpo_currency');
    }

    /**
     * Checks if currency is one of the local currencies.
     *
     * @return bool
     */
    public function isDefault()
    {
        return $this->getCode() === static::getDefaultCurrencyCode();
    }

    public static function hasLocalCurrencies(): bool
    {
        return (bool) count(sys_get('list:local_currencies'));
    }

    /**
     * Get a list of all the local currencies.
     *
     * @return \Ds\Domain\Commerce\Currency[]
     */
    public static function getLocalCurrencies(): array
    {
        return collect(static::getDefaultCurrencyCode())
            ->merge(sys_get('list:local_currencies'))
            ->map(function ($code) {
                return new static($code);
            })->sort()
            ->keyBy('code')
            ->all();
    }

    /**
     * Does this site support multi-currency.
     *
     * @return bool
     */
    public static function hasMultipleCurrencies()
    {
        $currencies = static::getLocalCurrencies();

        return count($currencies) > 1 ? true : false;
    }

    /**
     * Checks if currency is one of the local currencies.
     *
     * @return bool
     */
    public function isLocal()
    {
        $currencies = static::getLocalCurrencies();

        return (bool) collect($currencies)->firstWhere('code', $this->code);
    }

    /**
     * Get the currency name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->data['name'];
    }

    /**
     * Get the ISO code.
     *
     * @return string
     */
    public function getCode()
    {
        return $this->data['code'];
    }

    /**
     * Get the currency symbol.
     *
     * @return string
     */
    public function getSymbol()
    {
        return $this->data['symbol_native'];
    }

    /**
     * Check if the currency has a unique symbol.
     *
     * @return bool
     */
    public function hasUniqueSymbol()
    {
        $currencies = static::getLocalCurrencies();

        return collect($currencies)->where('symbol', $this->getSymbol())->count() < 2;
    }

    /**
     * Get a unique symbol for the currency.
     *
     * @return string
     */
    public function getUniqueSymbol()
    {
        if ($this->hasUniqueSymbol()) {
            return $this->getSymbol();
        }

        return $this->getCode();
    }

    /**
     * Get the currenty subunit
     */
    public function getSubunit(): int
    {
        return $this->data['subunit'] ?: 100;
    }

    /**
     * Get the countries that use currency.
     *
     * @return string
     */
    public function getCountries()
    {
        return $this->data['countries'];
    }

    public function getDefaultCountry(): ?string
    {
        return $this->data['country'];
    }

    /**
     * Get the exchange rate.
     *
     * @return float
     */
    public function getRate()
    {
        return static::getExchangeRate(null, $this->getCode());
    }

    /**
     * Get the underlying currency data.
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Determine if an attribute exists for currency.
     *
     * @param string $key
     * @return bool
     */
    public function __isset(string $key): bool
    {
        return array_key_exists($key, $this->attributeMap);
    }

    /**
     * Get an item with a given key.
     *
     * @param string $key
     * @return mixed
     */
    public function __get(string $key)
    {
        if ($this->attributeMap[$key] ?? false) {
            return $this->{$this->attributeMap[$key]}();
        }

        return null;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return collect($this->attributeMap)
            ->mapWithKeys(fn ($value, $key) => [$key => $this->{$value}()])
            ->all();
    }

    /**
     * Liquid representation of currency.
     */
    public function toLiquid()
    {
        return new CurrencyDrop($this);
    }

    /**
     * Return a PHP Currency object.
     *
     * @return \Money\Currency
     */
    public function toPhpCurrency(): PhpCurrency
    {
        return new PhpCurrency($this->getCode());
    }

    /**
     * Convert into something JSON serializable.
     *
     * @return string
     */
    public function jsonSerialize(): string
    {
        return $this->getCode();
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param int $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * Dump the currency.
     *
     * @return array
     */
    public function __debugInfo()
    {
        return $this->toArray();
    }

    /**
     * Output as a string.
     */
    public function __toString()
    {
        return $this->getCode();
    }

    /**
     * Get the ISO-4217 data on currencies.
     *
     * @return array
     */
    public static function getCurrencies()
    {
        return dataset('iso.4217');
    }
}
