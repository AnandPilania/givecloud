<?php

namespace Ds\Common\Fractal;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class FractalServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('fractal', function ($app) {
            $manager = new Manager;
            $manager->setSerializer(new ArraySerializer);

            return $manager;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['fractal'];
    }
}
