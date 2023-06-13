<?php

use Ds\Domain\FeaturePreviews\FeaturePreviewsService;
use Ds\Domain\Shared\Exceptions\MessageException;
use Ds\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

function isDev()
{
    return app()->environment('local');
}

function public_path($path = '')
{
    if (Str::startsWith($path, 'vendor/')) {
        return base_path("public/jpanel/assets/$path");
    }

    return app()->make('path.public') . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : $path);
}

function mix($path, $manifestDirectory = '')
{
    if (Str::startsWith($manifestDirectory, 'vendor/')) {
        $manifestDirectory = preg_replace('#vendor/#', 'jpanel/assets/vendor/', $manifestDirectory);

        return "$manifestDirectory/$path";
    }

    return app(\Illuminate\Foundation\Mix::class)($path, $manifestDirectory);
}

function omniscient(object $value)
{
    return new \Ds\Domain\Shared\Support\Omniscient($value);
}

function rescueQuietly(callable $callback, $rescue = null)
{
    return rescue($callback, $rescue, false);
}

function isGivecloudExpress(): bool
{
    return sys_get('use_givecloud_express') || optional(site()->plan)->name === 'GCX';
}

function isGivecloudPro(): bool
{
    return ! isGivecloudExpress();
}

function gravatar(?string $email, string $d = '404'): string
{
    $hash = md5(strtolower(trim($email)));

    return "https://www.gravatar.com/avatar/{$hash}?d={$d}";
}

// for use in our old school .html.php templates and DataTable setFormatRowFunction
// to indicate output that is marked as intentially not escaped
function dangerouslyUseHTML($value)
{
    return $value;
}

function dataset(string $key): array
{
    $path = database_path(sprintf('datasets/%s.php', strtr($key, '.', '/')));

    if (file_exists($path)) {
        return include $path;
    }

    return [];
}

function data_coalesce($target, $default = null, $keys = false)
{
    if ($keys === false) {
        $keys = $default;
        $default = null;
    }
    if (is_string($keys)) {
        $keys = explode(',', $keys);
    }
    foreach (Arr::wrap($keys) as $key) {
        $value = data_get($target, $key);
        if ($value !== null) {
            return $value;
        }
    }

    return $default;
}

function shortlink($url, $linkable = null)
{
    return app(\Ds\Domain\MissionControl\ShortlinkService::class)->make($url, $linkable);
}

function gc_profile($iterations, $closure = null)
{
    if (is_callable($iterations)) {
        $closure = $iterations;
        $iterations = 1;
    }
    if (! is_int($iterations) || ! is_callable($closure)) {
        throw new \InvalidArgumentException;
    }
    $start = microtime(true);
    foreach (range(1, max(1, $iterations)) as $index) {
        $closure();
    }
    $duration = microtime(true) - $start;

    return "Completed in {$duration}\n";
}

function dbq()
{
    return app('db.query-listener');
}

function notifyError($name, $message, $callback = null)
{
    app('exceptionist')->notifyError($name, $message, $callback);
}

function notifyException(Throwable $e, $callback = null)
{
    app('exceptionist')->notifyException($e, $callback);
}

function nullable_cast($type, $value)
{
    if ($value !== null) {
        settype($value, $type);

        return $value;
    }
}

function safe_explode($delimiter, $string, $limit = null)
{
    if ($limit) {
        return array_pad(explode($delimiter, $string, $limit), $limit, '');
    }

    return explode($delimiter, $string);
}

function explode_ids($string, $delimiter = ',')
{
    $ids = explode($delimiter, $string);
    $ids = array_map('trim', $ids);
    $ids = array_filter($ids, 'strlen');

    return array_map('intval', $ids);
}

function reqcache($arg1 = null, $arg2 = null)
{
    $blink = app(\Ds\Domain\Shared\BlinkCache::class);

    if (empty($arg1) && empty($arg2)) {
        return $blink;
    }

    if (is_array($arg1)) {
        foreach ($arg1 as $key => $value) {
            $blink->remember($key, $value);
        }

        return true;
    }

    if (is_string($arg1) && $arg2 === null) {
        return $blink->get($arg1);
    }

    if (is_string($arg1)) {
        return $blink->remember($arg1, $arg2);
    }

    throw new \InvalidArgumentException;
}

/**
 * Returns an array of global merge tags available on anything using merge tags.
 */
function global_merge_tags()
{
    return [
        'all_fundraising_pages_url' => secure_site_url('fundraisers'),
        'history_url' => secure_site_url('account/history'),
        'login_url' => secure_site_url('account/login'),
        'my_fundraising_pages_url' => secure_site_url('account/fundraisers'),
        'organization_name' => sys_get('clientName'),
        'payment_methods_url' => secure_site_url('account/payment-methods'),
        'profile_url' => secure_site_url('my-profile.php'),
        'recurring_payments_url' => secure_site_url('account/subscriptions'),
        'register_url' => secure_site_url('account/register'),
        'site_url' => secure_site_url(''),
        'sponsorships_url' => secure_site_url('account/sponsorships'),

        // legacy tags
        'shoporganization' => sys_get('clientName'),
        'shopurl' => secure_site_url(''),
        'shop_organization' => sys_get('clientName'),
        'shop_url' => secure_site_url(''),
    ];
}

function is_instanceof($object, $classList): bool
{
    foreach (Arr::wrap($classList) as $class) {
        if (is_object($object) && is_a($object, $class)) {
            return true;
        }
    }

    return false;
}

function feature($name)
{
    if (isGivecloudExpress()) {
        $gcxFeatures = app(\Ds\Domain\Settings\GivecloudExpressConfigRepository::class)->getAvailableFeatures();

        if (! in_array($name, $gcxFeatures, true)) {
            return false;
        }
    }

    if ($name === 'givecloud_pro') {
        return true;
    }

    if ($feature = app(FeaturePreviewsService::class)->get('feature_' . $name)) {
        return $feature->isEnabled();
    }

    return sys_get('feature_' . $name) == 1;
}

function number_suffix($num)
{
    if (! in_array(($num % 100), [11, 12, 13])) {
        switch ($num % 10) {
            // Handle 1st, 2nd, 3rd
            case 1:  return $num . 'st';
            case 2:  return $num . 'nd';
            case 3:  return $num . 'rd';
        }
    }

    return $num . 'th';
}

function string_substituteFromArray($body = '', $data = [])
{
    // if no data is passed in, return the body
    if (empty($data)) {
        return $body;
    }

    // loop through the data and replace each key w/ its value
    foreach ($data as $key => $val) {
        if (is_array($val)) {
            continue;
        }

        if (is_instanceof($val, \Ds\Domain\Shared\Date::class)) {
            $val = fromLocalFormat($val, 'M j, Y');
        } elseif (is_instanceof($val, \DateTime::class)) {
            $val = fromLocalFormat($val, 'r');
        } elseif (is_object($val)) {
            if (method_exists($val, '__toString')) {
                $val = (string) $val;
            } else {
                continue;
            }
        }

        $body = str_replace('[[' . $key . ']]', $val, $body);
    }

    // return the new string
    return $body;
}

function day_of_week($day_of_week)
{
    $payment_day_names = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

    return $payment_day_names[$day_of_week - 1];
}

function str_putcsv(array $fields, $delimiter = ',', $enclosure = '"', $escape_char = '\\')
{
    $fp = fopen('php://memory', 'r+');
    if (fputcsv($fp, $fields, $delimiter, $enclosure, $escape_char) === false) {
        return '';
    }
    rewind($fp);
    $csv = stream_get_contents($fp);
    fclose($fp);

    return rtrim($csv);
}

/* @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ */
/* @@@@@@@@@@ from required.php @@@@@@@@@ */
/* @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ */

function money($amount, $currencyCode = null, $inSubunits = false)
{
    return new \Ds\Domain\Commerce\Money($amount, $currencyCode, $inSubunits);
}

function currency($currencyCode = null)
{
    return new \Ds\Domain\Commerce\Currency($currencyCode);
}

function numeral($value)
{
    return new \Ds\Common\Numeral($value);
}

function numeralFormat($value, $format = '0,0.00')
{
    $value = new \Ds\Common\Numeral($value);

    return ($value->toFloat() === null) ? '' : $value->format($format);
}

/**
 * Render a Liquid string.
 *
 * @param string $source
 * @param array $assigns
 * @return string
 */
function liquid(string $source, array $assigns = [], string $name = null): string
{
    return \Ds\Domain\Theming\Liquid\Template::renderLiquid($source, $assigns, $name);
}

/**
 * Search content for shortcodes and filter shortcodes through their hooks.
 *
 * @param string $content
 * @return string
 */
function do_shortcode($content)
{
    return app('shortcodes')->render($content);
}

function stri_slug($title, $separator = '-')
{
    $title = \Illuminate\Support\Str::ascii($title, 'en');

    // Convert all dashes/underscores into separator
    $flip = $separator == '-' ? '_' : '-';

    $title = preg_replace('![' . preg_quote($flip) . ']+!u', $separator, $title);

    // Replace @ with the word 'at'
    $title = str_replace('@', $separator . 'at' . $separator, $title);

    // Remove all characters that are not the separator, letters, numbers, or whitespace.
    $title = preg_replace('![^' . preg_quote($separator) . '\pL\pN\s]+!u', '', $title);

    // Replace all separator characters and whitespace by a single separator
    $title = preg_replace('![' . preg_quote($separator) . '\s]+!u', $separator, $title);

    return trim($title, $separator);
}

function sanitize_filename($title)
{
    $title = \Illuminate\Support\Str::ascii($title, 'en');

    // Replace @ with the word 'at'
    $title = str_replace('@', '-at-', $title);

    // Remove all characters that are not dashes, periods, letters, numbers, or whitespace.
    $title = preg_replace('![^-_.\pL\pN\s]+!u', '', mb_strtolower($title));

    // Replace all dashes, underscores and whitespace with a single dash
    $title = preg_replace('![-_\s]+!u', '-', $title);

    // Remove period adjacent dashes
    $title = str_replace(['-.', '.-'], '.', $title);

    // Replace all periods with a single period
    $title = preg_replace('![.]+!u', '.', $title);

    return trim($title, '-');
}

function jpanel_asset_url($path, bool $cacheBuster = true)
{
    $path = ltrim($path, '/');
    $baseUrl = '/jpanel/assets';

    if (app()->isLocal() && file_exists(public_path('hot'))) {
        if (Str::startsWith($path, 'apps/admin/')) {
            $baseUrl = 'https://_hot.givecloud.test:5550/jpanel/assets';
        }
    }

    if ($cacheBuster) {
        try {
            $timestamp = filemtime(base_path("public/jpanel/assets/$path"));
        } catch (\ErrorException $e) {
            $timestamp = time();
        }

        return "{$baseUrl}/{$path}?v=" . substr(sha1($timestamp), 0, 10);
    }

    return "{$baseUrl}/{$path}";
}

function app_asset_url($path, bool $cacheBuster = true)
{
    $path = ltrim($path, '/');
    $baseUrl = '/assets/apps';

    if (app()->isLocal() && file_exists(public_path('hot'))) {
        if (Str::startsWith($path, 'donation-forms/')) {
            $baseUrl = 'https://_hot.givecloud.test:5551/assets/apps';
        } elseif (Str::startsWith($path, 'embeddable-form/donate/')) {
            $baseUrl = 'https://_hot.givecloud.test:5552/assets/apps';
        } elseif (Str::startsWith($path, 'peer-to-peer/')) {
            $baseUrl = 'https://_hot.givecloud.test:5553/assets/apps';
        } elseif (Str::startsWith($path, 'virtual-events/')) {
            $baseUrl = 'https://_hot.givecloud.test:5554/assets/apps';
        }
    }

    if ($cacheBuster) {
        try {
            $timestamp = filemtime(base_path("public/assets/apps/$path"));
        } catch (\ErrorException $e) {
            $timestamp = time();
        }

        return "{$baseUrl}/{$path}?v=" . substr(sha1($timestamp), 0, 10);
    }

    return "{$baseUrl}/{$path}";
}

function user($value = null)
{
    $user = auth()->user();
    if ($user) {
        if (is_null($value)) {
            return $user;
        }

        return $user->{$value} ?? null;
    }
    if ($value) {
        return null;
    }

    return new \Ds\AnonymousUserStub;
}

function remove_php_extension_from_url($path, $checkRequest = false)
{
    if (request()->isMethod('get')) {
        if ($checkRequest && Str::startsWith(request()->server('REQUEST_URI'), '/index.php')) {
            $path = secure_site_url('/');
        } elseif (Str::endsWith($path, '.php')) {
            $path = substr($path, 0, strlen($path) - 4);
        } elseif (! $checkRequest || ! request()->is('*.php')) {
            return;
        }

        if ($query = request()->server('QUERY_STRING')) {
            $path .= "?{$query}";
        }

        throw new \Ds\Domain\Shared\Exceptions\RedirectException($path, 301);
    }
}

function db_real_escape_string($value)
{
    return substr(DB::getPdo()->quote((string) $value), 1, -1);
}

function db_escape_like($value, $char = '\\')
{
    return str_replace(
        [$char, '%', '_'],
        [$char . $char, $char . '%', $char . '_'],
        $value
    );
}

/**
 * Wrapper around old mysql_query calls.
 *
 * @param string $query
 * @param mixed ...$args
 * @return \PDOStatement|bool
 */
function db_query($query, ...$args)
{
    // handle params passed in as an array
    // and params passed in as arguments
    if (count($args) === 1 && is_array($args[0])) {
        $args = $args[0];
    }
    // replace vsprintf conversion specifications with
    // positional placeholders
    if (count($args)) {
        $query = str_replace("'%s'", '?', $query);
        $query = str_replace('"%s"', '?', $query);
        $query = preg_replace('/(?<!%)%(?:[-+#0])?(?:\d+|\*)?(?:\.(?:\d+|\*))?(?:[hljztL]|hh|ll)?(?:[diuoxXfFeEgGaAcspn])/', '?', $query);
    }

    try {
        return DB::pdoQuery($query, $args);
    } catch (\Illuminate\Database\QueryException $e) {
        notifyException($e);

        // Ideally we should throw the exception however to prevent
        // regression issues we are just silently report the exception.
        // This will maintain a consistent behaviour with the old mysql_query
        // function that this is replacing.
        return false;
    }
}

function db_var($query)
{
    $args = func_get_args();
    if ($result = call_user_func_array('db_query', $args)) {
        return $result->fetchColumn(0) ?: null;
    }
}

function db_num_rows(PDOStatement $result)
{
    return $result->rowCount();
}

function db_fetch_assoc(PDOStatement $result)
{
    return $result->fetch(\PDO::FETCH_ASSOC);
}

function db_fetch_object(PDOStatement $result)
{
    return $result->fetch(\PDO::FETCH_OBJ);
}

function send_using_swiftmailer(Swift_Message $message)
{
    dispatch(new \Ds\Jobs\SendEmail($message));

    return true;
}

function uuid(Closure $generator = null)
{
    do {
        if ($generator) {
            $uuid = $generator();
        } else {
            $uuid = strtoupper(bin2hex(random_bytes(5)));
        }
    } while (db_var('SELECT client_uuid FROM productorder WHERE client_uuid = %s LIMIT 1', $uuid));

    return $uuid;
}

function is_super_user(User $user = null)
{
    if (empty($user)) {
        $user = user();
    }

    return $user->id === config('givecloud.super_user_id');
}

function card_type_from_first_number($num_str)
{
    $first_char = substr($num_str, 0, 1);

    // http://stackoverflow.com/questions/72768/how-do-you-detect-credit-card-type-based-on-number
    if ($first_char == '4') {
        return 'Visa';
    }

    if ($first_char == '5') {
        return 'MasterCard';
    }

    if ($first_char == '3') {
        return 'American Express';
    }

    // if ($first_char == '3') return 'Diners Club';

    if ($first_char == '6') {
        return 'Discover';
    }

    if ($first_char == '2' || $first_char == '1') {
        return 'JCB';
    }

    return '';
}

function query_to_dated_array(PDOStatement $query, $start_at, $end_at, $col_date, $col_1, $col_2 = null, $col_3 = null)
{
    $records = [];

    $curr_date = toLocal($start_at);
    $end_date = toLocal($end_at);

    while ($curr_date->lte($end_date)) {
        $key = $curr_date->format('Y-m-d');
        $records[$key] = (object) [$col_date => $key];

        if ($col_1) {
            $records[$key]->{$col_1} = 0;
        }

        if ($col_2) {
            $records[$key]->{$col_2} = 0;
        }

        if ($col_3) {
            $records[$key]->{$col_3} = 0;
        }

        $curr_date->addDay();
    }

    if (db_num_rows($query)) {
        while ($row = $query->fetch(\PDO::FETCH_BOTH)) {
            $key = toLocalFormat($row[$col_date], 'Y-m-d');
            if (array_key_exists($key, $records)) {
                if ($col_1) {
                    $records[$key]->{$col_1} += (float) $row[$col_1];
                }

                if ($col_2) {
                    $records[$key]->{$col_2} += (float) $row[$col_2];
                }

                if ($col_3) {
                    $records[$key]->{$col_3} += (float) $row[$col_3];
                }
            }
        }
    }

    return array_values($records);
}

function ua_formatted($ua_str)
{
    return app('ua')->parse($ua_str)->toString();
}

function ua_browser($ua_str)
{
    $ua = app('ua')->parse($ua_str)->ua;

    return $ua->family . ' ' . $ua->major;
}

function ua_os($ua_str)
{
    return app('ua')->parse($ua_str)->os->family;
}

function flash($key, $value = null)
{
    if ($value === null) {
        return session($key);
    }
    session()->flash($key, $value);
}

function is_jpanel_route()
{
    return request()->is(['jpanel', 'jpanel/*']);
}

/**
 * Central function for formatting addresses
 */
function address_format($address1 = '', $address2 = '', $city = '', $state = '', $zip = '', $country = '', $seperator = "\n")
{
    $lines = [];

    if (trim($address1)) {
        $lines[] = trim($address1);
    }

    if (trim($address2)) {
        $lines[] = trim($address2);
    }

    $line3 = [];

    if (trim($city)) {
        $line3[] = trim($city);
    }

    if (trim($state)) {
        $line3[] = trim($state);
    }

    if (trim($zip)) {
        $line3[] = trim($zip);
    }

    if (trim($country)) {
        $line3[] = trim($country);
    }

    $line3 = implode(', ', $line3);

    if (trim($line3)) {
        $lines[] = $line3;
    }

    return implode($seperator, $lines);
}

/**
 * Return a string as a phone number.
 *
 * @param mixed $s
 * @return string|null
 */
function phone_format($s)
{
    $rx = "/
        (1)?\D*     # optional country code
        (\d{3})?\D* # optional area code
        (\d{3})\D*  # first three
        (\d{4})     # last four
        (?:\D+|$)   # extension delimiter or EOL
        (\d*)       # optional extension
    /x";
    preg_match($rx, $s, $matches);

    if (! isset($matches[0])) {
        return null;
    }

    $country = $matches[1];
    $area = $matches[2];
    $three = $matches[3];
    $four = $matches[4];
    $ext = $matches[5];

    $out = "$three-$four";
    if (! empty($area)) {
        $out = "$area-$out";
    }

    if (! empty($country)) {
        $out = "+$country-$out";
    }

    if (! empty($ext)) {
        $out .= "x$ext";
    }

    // check that no digits were truncated
    // if (preg_replace('/\D/', '', $s) != preg_replace('/\D/', '', $out)) return false;
    return $out;
}

/**
 * Grab the active site record from the backend
 */
function site($attr = null)
{
    $site = app(\Ds\Domain\MissionControl\MissionControlService::class)->getSite();

    if ($attr === null) {
        return $site;
    }

    return $site->{$attr} ?? null;
}

/**
 * Get secure URL for the site.
 *
 * @param string $path
 * @return string
 */
function secure_site_url($path = '', $useSubdomain = false)
{
    // passthrough HTTP(S) paths
    if (Str::startsWith($path, ['http://', 'https://'])) {
        return $path;
    }

    $domain = reqcache(
        'secure_domain:' . (int) $useSubdomain,
        function () use ($useSubdomain) {
            if ($useSubdomain) {
                return site()->subdomain;
            }

            $domain = request()->getHost();
            if (site()->isDomainSslEnabled($domain)) {
                return $domain;
            }

            return site()->secure_domain;
        }
    );

    return rtrim("https://$domain/" . ltrim($path, '/'), '/');
}

/**
 * Server-side google analytics tracking.
 *
 * @param array $params
 * @param bool $debug
 * @return void
 */
function google_analytics_event(array $params = null, $debug = false)
{
    // make sure property id exists
    if (sys_get('webStatsPropertyId') == '') {
        throw new MessageException('Google Property ID has not been configured for this client. (webStatsPropertyId is blank)');
    }

    // make sure property id exists
    if (! isset($params)) {
        throw new MessageException('No parameters were supplied for tracking.');
    }

    // http client
    $google_endpoint = ($debug) ? 'http://www.google-analytics.com/debug/collect' : 'http://www.google-analytics.com/collect';

    // consistent google data
    $google_data = [
        'v' => 1,                             // version
        'tid' => sys_get('webStatsPropertyId'), // UA-XXXXX-Y
        'cid' => (request()->cookie('_ga')) ? request()->cookie('_ga') : Str::random(32), // try googles '_ga' cookie, otherwise a random string
        'uip' => request()->ip(),
        'ua' => request()->server('HTTP_USER_AGENT'),
    ];

    // prepare request
    $res = Http::withOptions([
        'query' => array_merge($google_data, $params),
        'timeout' => 5,
    ])->post($google_endpoint);
}

/**
 * Used in frontend templating.
 * Returns pagination data as an array.
 *
 * @param \Illuminate\Pagination\LengthAwarePaginator $paged_collection
 * @param array $filters
 * @return \stdClass
 */
function get_pagination_data($paged_collection, $filters = null)
{
    // if there are filter vars, include them in the paginate URLs
    if ($filters) {
        $paged_collection->appends($filters);
    }

    // return paging params
    return (object) [
        'links' => $paged_collection->links(),
        'count' => $paged_collection->count(),
        'currentPage' => $paged_collection->currentPage(),
        'firstItem' => $paged_collection->firstItem(),
        'hasMorePages' => $paged_collection->hasMorePages(),
        'lastItem' => $paged_collection->lastItem(),
        'lastPage' => $paged_collection->lastPage(),
        'nextPageUrl' => $paged_collection->nextPageUrl(),
        'perPage' => $paged_collection->perPage(),
        'previousPageUrl' => $paged_collection->previousPageUrl(),
        'total' => $paged_collection->total(),
    ];
}

/**
 * Get a CONSISTENT friendly file download name.
 * Returns (DATE)_(ACCOUNT_NAME)_(FILENAME.XXX)
 *
 * Example: 20170119_072334_bcm_abandoned_carts.csv
 *
 * @param string $name Desired filename (end of the file name)
 * @return string
 */
function export_filename($name)
{
    return fromLocalFormat('now', 'Ymd\_His') . '_' . sys_get('ds_account_name') . '_' . $name;
}

function dataTableGroupBy($datatable, $builder)
{
    // get the original response from datatables
    $datatable_arr = $datatable->make();

    // simple aggregated query
    $true_count = $builder->selectRaw('count(*) as agg')->get()->count();

    // calculate our own totals
    $datatable_arr['recordsTotal'] = $true_count;
    $datatable_arr['recordsFiltered'] = $true_count;

    // return the patched array
    return $datatable_arr;
}

/**
 * Escapes a model's table name
 *
 * @param string $table_name The table name to escape
 * @return string
 */
function tbl($table_name)
{
    $tbl = explode('.', $table_name);

    return '`' . implode('`.`', $tbl) . '`';
}

/*

TIMEZONE FUNCTIONS

 */

/**
 * Return the timezone code.
 *
 * @param \DateTime|int|string|null $time
 * @return string
 */
function localTz($time = 'today')
{
    return toLocalFormat($time, 'T');
}

/**
 * Return the timezone offset hours
 *
 * @param \DateTime|int|string|null $time
 * @return string
 */
function localOffset($time = 'today')
{
    return toLocalFormat($time, 'P');
}

/**
 * Get Carbon instance that uses the site timezone and when parsing magical date/time
 * formatted strings or timestamps use the site timezone.
 *
 * @param \DateTime|int|string|null $time
 * @return \Ds\Domain\Shared\DateTime|null
 */
function fromLocal($time = null)
{
    if ($time = \Ds\Domain\Shared\DateTime::parseDateTime($time, 'local')) {
        return $time->toLocal();
    }
}

/**
 * Display formatted local time.
 *
 * @param \DateTime|int|string|null $time
 * @param string $format
 * @return string
 */
function fromLocalFormat($time = null, $format = 'auto')
{
    return formatDateTime(fromLocal($time), $format);
}

/**
 * Get Carbon instance that uses the site timezone and when parsing magical date/time
 * formatted strings or timestamps use UTC as the timezone.
 *
 * @param \DateTime|int|string|null $time
 * @return \Ds\Domain\Shared\DateTime|null
 */
function toLocal($time = null)
{
    if ($time = \Ds\Domain\Shared\DateTime::parseDateTime($time, 'UTC')) {
        return $time->toLocal();
    }

    return null;
}

/**
 * Display formatted local time.
 *
 * @param \DateTime|int|string|null $time
 * @param string $format
 * @return string
 */
function toLocalFormat($time = null, $format = 'auto')
{
    return formatDateTime(toLocal($time), $format);
}

/**
 * Get Carbon instance that uses UTC and when parsing magical date/time
 * formatted strings or timestamps use UTC as the timezone.
 *
 * @param \DateTime|int|string|null $time
 * @return \Ds\Domain\Shared\DateTime|null
 */
function fromUtc($time = null)
{
    if ($time = \Ds\Domain\Shared\DateTime::parseDateTime($time, 'UTC')) {
        return $time->toUtc();
    }

    return null;
}

/**
 * Display formatted UTC time.
 *
 * @param \DateTime|int|string|null $time
 * @param string $format
 * @return string
 */
function fromUtcFormat($time = null, $format = 'auto')
{
    return formatDateTime(fromUtc($time), $format);
}

/**
 * Get Carbon instance that uses UTC and when parsing magical date/time
 * formatted strings or timestamps use the site timezone.
 *
 * @param \DateTime|int|string|null $time
 * @return \Ds\Domain\Shared\DateTime|null
 */
function toUtc($time = null)
{
    if ($time = \Ds\Domain\Shared\DateTime::parseDateTime($time, 'local')) {
        return $time->toUtc();
    }

    return null;
}

/**
 * Display formatted UTC time.
 *
 * @param \DateTime|int|string|null $time
 * @param string $format
 * @return string
 */
function toUtcFormat($time = null, $format = 'auto')
{
    return formatDateTime(toUtc($time), $format);
}

/**
 * Get Carbon instance that uses UTC and when parsing magical date/time
 * formatted strings or timestamps use UTC as the timezone.
 *
 * @param \DateTime|int|string|null $time
 * @return \Ds\Domain\Shared\DateTime|null
 */
function fromDate($time = null)
{
    return \Ds\Domain\Shared\Date::parseDateTime($time);
}

/**
 * Display formatted UTC time.
 *
 * @param \DateTime|int|string|null $time
 * @param string $format
 * @return string
 */
function fromDateFormat($time = null, $format = 'auto')
{
    return formatDateTime(fromDate($time), $format);
}

/**
 * Format a DateTime.
 *
 * @param \DateTime|null $time
 * @param string $format
 * @return string
 */
function formatDateTime(DateTime $time = null, $format = 'auto')
{
    if ($time) {
        return \Ds\Domain\Shared\DateTime::parse($time)->format($format);
    }

    return '';
}

/**
 * Retuns an FA icon class based on a string.
 *
 * @param string $type
 * @return string|null
 */
function fa_ua_icon($type)
{
    switch (strtolower(trim($type))) {
        // browsers
        case 'safari':  return 'fa-safari';
        case 'mobile safari':  return 'fa-safari';
        case 'chrome':  return 'fa-chrome';
        case 'chrome mobile webview':  return 'fa-chrome';
        case 'firefox': return 'fa-firefox';
        case 'opera':   return 'fa-opera';
        case 'edge':
        case 'ie':
        case 'msie':
        case 'internet explorer': return 'fa-edge';
        // operating systems
        case 'os x': return 'fa-apple';
        case 'mac os x': return 'fa-apple';
        case 'ios': return 'fa-mobile';
        case 'windows':  return 'fa-windows';
        case 'android':  return 'fa-android';
        // device
        case 'other': return 'fa-desktop';
    }
}

/**
 * Retuns an FA icon class based on a string.
 *
 * @param string $type
 * @return string
 */
function fa_payment_icon($type)
{
    switch (strtolower(trim($type))) {
        case 'v':
        case 'vi':
        case 'visa': return 'fa-cc-visa';
        case 'm':
        case 'mc':
        case 'mastercard':
        case 'master card': return 'fa-cc-mastercard';
        case 'a':
        case 'am':
        case 'amex':
        case 'americanexpress':
        case 'american express': return 'fa-cc-amex';
        case 'd':
        case 'discover': return 'fa-cc-discover';
        case 'ach':
        case 'checking':
        case 'checkings':
        case 'saving':
        case 'savings':
        case 'business check':
        case 'business checking':
        case 'business savings':
        case 'personal check':
        case 'personal checking':
        case 'personal savings': return 'fa-bank';
        case 'vault': return 'fa-lock';
        case 'paypal': return 'fa-paypal';
        case 'cash': return 'fa-money';
        default: return 'fa-credit-card';
    }
}

/**
 * Retuns an FA icon class based on a string.
 *
 * @param string $type
 * @return string
 */
function fa_social_icon($type)
{
    $type = strtolower(trim($type));

    if (strpos($type, 'facebook') !== false) {
        return 'fa-facebook-official';
    }
    if (strpos($type, 'android') !== false) {
        return 'fa-android';
    }
    if (strpos($type, 'google') !== false) {
        return 'fa-google';
    }
    if (strpos($type, 'instagram') !== false) {
        return 'fa-instagram';
    }
    if (strpos($type, 'pinterest') !== false) {
        return 'fa-pinterest';
    }
    if (strpos($type, 'linkedin') !== false) {
        return 'fa-linkedin';
    }
    if (strpos($type, 'tumblr') !== false) {
        return 'fa-tumblr';
    }
    if (strpos($type, 'twitter') !== false) {
        return 'fa-twitter';
    }
    if (strpos($type, 'mail') !== false) {
        return 'fa-envelope-o';
    }
    if (strpos($type, 'constantcontact') !== false) {
        return 'fa-envelope-o';
    }

    return 'fa-globe';
}

/**
 * Returns the name of the partner
 */
function partner()
{
    return site()->partner->identifier ?? 'gc';
}

/**
 * Flag url
 */
function flag($country)
{
    $countries = cart_countries();
    if (strlen($country) == 2) {
        $country = $countries[strtoupper($country)] ?? 'default';
    }

    return 'https://cdn.givecloud.co/static/flag/' . Str::slug($country, '-') . '.png';
}

/**
 * Get the unicode string for a given country code.
 */
function flagUnicode(string $countryCode): string
{
    return implode('', array_map(function ($c) {
        return IntlChar::chr(IntlChar::ord(strtoupper($c)) + 0x1F1A5);
    }, str_split($countryCode)));
}

/**
 * Takes a URL and Attempts to fetch the embed HTML for it using oEmbed.
 *
 * @param string $url
 * @return array
 */
function oembed_get($url)
{
    $key = 'oembed:' . sha1($url);
    $url = 'https://app.givecloud.co/services/embed.json?url=' . urlencode($url);

    $cache = cache()->store('app');

    if (! $cache->has($key)) {
        try {
            $json = Http::get($url)->throw()->json();

            $cache->put($key, $json, now()->addHours(24));
        } catch (\Throwable $e) {
            $cache->put($key, null, now()->addMinutes(5));
        }
    }

    return $cache->get($key);
}
