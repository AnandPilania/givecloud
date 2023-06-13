<?php

namespace Ds\Common\DonorPerfect;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class DonorPerfectServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('dpo', function ($app) {
            $connection = new Connection($app['dpo.client']);
            $connection->setEventDispatcher($app['events']);

            return $connection;
        });

        $this->app->singleton('dpo.client', Client::class);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'dpo',
            'dpo.client',
        ];
    }
}
