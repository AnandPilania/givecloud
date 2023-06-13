<?php

namespace Ds\Domain\Analytics\Models;

use Ds\Domain\Analytics\UserAgent;
use Ds\Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class AnalyticsVisit extends Model
{
    /** @var array */
    protected $casts = [
        // stored in the visitors timezone in the database in order
        // to simplify performing time of day related queries without having
        // the need to do any timezone conversions. the UTC version of this
        // datetime is the `created_at` column in the database.
        'visitor_localtime' => 'datetime',

        'visitor_returning' => 'boolean',
        'visitor_count_visits' => 'integer',
        'visitor_days_since_last' => 'integer',
        'visitor_days_since_contribution' => 'integer',
        'visitor_days_since_first' => 'integer',
        'visit_total_events' => 'integer',
        'visit_total_time' => 'integer',
        'visit_contribution_converted' => 'boolean',
        'location_latitude' => 'float',
        'location_longitude' => 'float',
    ];

    public function analyticsEvents(): HasMany
    {
        return $this->hasMany(AnalyticsEvent::class);
    }

    public function setConfigUserAgentAttribute(?string $value): void
    {
        $userAgent = new UserAgent($value);

        $this->config_type = $userAgent->type;
        $this->config_browser_name = $userAgent->browserName;
        $this->config_browser_version = $userAgent->browserVersion;
        $this->config_platform_name = $userAgent->platformName;
        $this->config_platform_version = $userAgent->platformVersion;
        $this->config_device_name = $userAgent->deviceName;
        $this->config_device_brand = $userAgent->deviceBrand;
        $this->config_bot_name = $userAgent->botName;
        $this->attributes['config_user_agent'] = $userAgent->userAgentString;
    }

    public function setLocationIpAttribute(?string $value): void
    {
        $location = rescueQuietly(fn () => app('geoip')->getLocationData($value));

        $this->location_city = $location->city ?? null;
        $this->location_state = $location->state ?? null;
        $this->location_country = $location->iso_code ?? null;
        $this->location_latitude = $location->lat ?? null;
        $this->location_longitude = $location->lon ?? null;
        $this->location_timezone = $location->timezone ?? null;
        $this->attributes['location_ip'] = $value ?: null;
    }

    public function setReferrerUrlAttribute(?string $value): void
    {
        $this->referrer_name = parse_url((string) $value, PHP_URL_HOST) ?: '(direct)';
        $this->attributes['referrer_url'] = $value ?: null;
    }

    public function setVisitorAttributes(): void
    {
        $data = static::query()
            ->select([
                DB::raw('IF(COUNT(id) > 0, 1, 0) as visitor_returning'),
                DB::raw('COUNT(id) as visitor_count_visits'),
                DB::raw('IF(COUNT(id) > 0, DATEDIFF(NOW(), MAX(created_at)), NULL) as visitor_days_since_last'),
                DB::raw('NULL as visitor_days_since_contribution'), // TODO: update with actually data
                DB::raw('IF(COUNT(id) > 0, DATEDIFF(NOW(), MIN(created_at)), 0) as visitor_days_since_first'),
            ])->where('visitor_id', $this->visitor_id)
            ->where('created_at', '<', $this->created_at)
            ->toBase()
            ->first();

        $this->visitor_localtime = $this->location_timezone
            ? fromUtc($this->created_at)->setTimezone($this->location_timezone)
            : fromUtc($this->created_at);

        $this->visitor_returning = $data->visitor_returning;
        $this->visitor_count_visits = $data->visitor_count_visits;
        $this->visitor_days_since_last = $data->visitor_days_since_last;
        $this->visitor_days_since_contribution = $data->visitor_days_since_contribution;
        $this->visitor_days_since_first = $data->visitor_days_since_first;
    }
}
