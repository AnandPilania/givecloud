<?php

namespace Ds\Domain\MissionControl\Models;

use Ds\Common\DataAccess;
use Ds\Domain\MissionControl\MissionControlService;
use Ds\Services\SiteService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Traits\ForwardsCalls;

class Site extends DataAccess
{
    use ForwardsCalls;

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'custom_domains' => 'json',
        'ordered_on' => 'datetime',
        'product_count' => 'integer',
        'order_count' => 'integer',
        'total_sales' => 'double',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Check if the site is active.
     *
     * @return bool
     */
    public function getIsActiveAttribute()
    {
        return $this->status === 'ACTIVE';
    }

    /**
     * Check if the site is suspended.
     *
     * @return bool
     */
    public function getIsSuspendedAttribute()
    {
        return $this->status === 'SUSPENDED';
    }

    /**
     * Get the custom domain names for the site.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCustomDomainsAttribute()
    {
        return $this->domains->pluck('name');
    }

    /**
     * Get the Givecloud subdomain for the site.
     *
     * @return string
     */
    public function getSubdomainAttribute()
    {
        $domain = config('givecloud.sites_domain');

        if ($this->created_at->gt(fromUtc('2017-06-01')) && ! App::environment('local', 'testing')) {
            $domain = 'givecloud.co';
        }

        return "$this->ds_account_name.$domain";
    }

    /**
     * Get the supported subdomains for the site.
     *
     * @return array
     */
    public function getSubdomainsAttribute()
    {
        $domains = [
            "$this->ds_account_name.donorshops.com",
            "$this->ds_account_name.givecloud.co",
        ];

        if (in_array($this->subdomain, $domains)) {
            return $domains;
        }

        return [$this->subdomain];
    }

    /**
     * Get the domain name for the site.
     *
     * @return string
     */
    public function getDomainAttribute()
    {
        if (app()->runningInConsole()) {
            return $this->subdomain;
        }

        return app('request')->getHost();
    }

    /**
     * Get the primary domain name for the site.
     *
     * @return string
     */
    public function getPrimaryDomainAttribute()
    {
        $domain = sys_get('clientDomain');

        if ($domain) {
            $domain = $this->domains->firstWhere('name', $domain);
        } else {
            $domain = $this->domains->first();
        }

        return data_get($domain, 'name', $this->subdomain);
    }

    /**
     * Get the secure domain names for the site.
     */
    public function getSecureDomainsAttribute(): array
    {
        return array_merge(
            $this->subdomains,
            $this->domains
                ->where('ssl_enabled', true)
                ->pluck('name')
                ->all()
        );
    }

    /**
     * Get the secure domain name for the site.
     *
     * @return string
     */
    public function getSecureDomainAttribute()
    {
        if (sys_get('custom_domain_migration_mode')) {
            if (in_array($this->domain, $this->subdomains)) {
                return $this->domain;
            }

            $domain = $this->domains
                ->where('name', $this->domain)
                ->where('ssl_enabled', true)
                ->first();

            if ($domain) {
                return $domain->name;
            }
        }

        $domain = $this->domains
            ->where('name', $this->primary_domain)
            ->where('ssl_enabled', true)
            ->first();

        return data_get($domain, 'name', $this->subdomain);
    }

    /**
     * The first custom domain in the list is considered the primary
     * custom domain.
     *
     * @return string|null
     */
    public function getCustomDomainAttribute()
    {
        if (count($this->custom_domains)) {
            return $this->custom_domains->first();
        }
    }

    /**
     * Whether the primary domain is SSL enabled.
     *
     * @return bool
     */
    public function getPrimaryDomainSslEnabledAttribute()
    {
        return $this->isDomainSslEnabled($this->primary_domain);
    }

    /**
     * Whether the primary domain is SSL enabled.
     *
     * @return bool
     */
    public function isDomainSslEnabled($domain)
    {
        if (in_array($domain, $this->subdomains)) {
            return true;
        }

        $enabled = $this->domains
            ->where('name', $domain)
            ->where('ssl_enabled', true)
            ->first();

        return (bool) $enabled;
    }

    /**
     * The CDN path prefix.
     *
     * @return string
     */
    public function getCdnPathPrefixAttribute()
    {
        $id = str_pad($this->id, 8, '0', STR_PAD_LEFT);
        $id = substr($id, 0, 4) . '/' . substr($id, 4, 4);

        // Prevent development sites from writing
        // files and images to production site folders
        if (isDev()) {
            return "s/files/1-dev/$id";
        }

        return "s/files/1/$id";
    }

    /**
     * Get the authoritative site.
     *
     * @return \Ds\Domain\MissionControl\Models\Site|null
     */
    public function getAuthoritativeSiteAttribute(): ?Site
    {
        return app(MissionControlService::class)->getAuthoritativeSite();
    }

    /**
     * Attribute Accessor: Direct Billing Enabled
     *
     * @return bool
     */
    public function getDirectBillingEnabledAttribute()
    {
        return (bool) $this->client->direct_billing_enabled ?? false;
    }

    /**
     * Attribute Mutator: Client
     *
     * @param \Ds\Domain\MissionControl\Models\Client|null $value
     */
    public function setClientAttribute($value)
    {
        $this->attributes['client'] = $value ? new Client((array) $value) : null;
    }

    /**
     * Attribute Accessor: Domains
     *
     * @param \Illuminate\Support\Collection $value
     */
    public function getDomainsAttribute($value)
    {
        return collect($value);
    }

    /**
     * Attribute Mutator: Domains
     *
     * @param array|null $value
     */
    public function setDomainsAttribute($value)
    {
        $this->attributes['domains'] = collect($value)->map(function ($domain) {
            return new SiteDomain((array) $domain);
        });
    }

    /**
     * Attribute Mutator: Subscription
     *
     * @param \Ds\Domain\MissionControl\Models\Subscription|null $value
     */
    public function setSubscriptionAttribute($value)
    {
        $this->attributes['subscription'] = $value ? new Subscription((array) $value) : null;
    }

    /**
     * Handle dynamic method calls into the model.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->forwardCallTo(app(SiteService::class), $method, $parameters);
    }
}
