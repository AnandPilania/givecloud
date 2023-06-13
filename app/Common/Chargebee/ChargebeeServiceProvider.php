<?php

namespace Ds\Common\Chargebee;

use ChargeBee\ChargeBee\Environment as ChargeBeeEnvironment;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class ChargebeeServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('chargebee', function ($app) {
            ChargeBeeEnvironment::configure(
                $app['config']->get('services.chargebee.site'),
                $app['config']->get('services.chargebee.key')
            );

            return new Manager($app);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'chargebee',
        ];
    }
}
