<?php

namespace Ds\Common\ISO3166;

use Illuminate\Support\ServiceProvider;

class ISO3166ServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('iso3166', function ($app) {
            return new ISO3166($this->app['translator']);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['iso3166'];
    }
}
