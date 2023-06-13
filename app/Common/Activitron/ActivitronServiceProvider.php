<?php

namespace Ds\Common\Activitron;

use Illuminate\Support\ServiceProvider;

class ActivitronServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('activitron', function ($app) {
            return new Activitron($app['dogstatsd'], $app['intercom']);
        });

        $this->app->singleton('dogstatsd', function ($app) {
            return new \DataDog\DogStatsd([
                'api_key' => $app->config['services.datadog.api_key'],
                'app_key' => $app->config['services.datadog.app_key'],
            ]);
        });

        $this->app->singleton('intercom', function ($app) {
            return new \Intercom\IntercomClient((string) $app->config['services.intercom.access_token'], null);
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
            'activitron',
            'dogstatsd',
        ];
    }
}
