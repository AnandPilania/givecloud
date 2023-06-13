<?php

namespace Ds\Domain\Commerce\Support\TaxCloud;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class TaxCloudServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('taxCloud', function ($app) {
            return new TaxCloud([
                'endpoint' => 'https://api.taxcloud.net/1.0/TaxCloud/',
                'api_key' => sys_get('taxcloud_api_key'),
                'api_login_id' => sys_get('taxcloud_api_login_id'),
            ]);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['taxCloud'];
    }
}
