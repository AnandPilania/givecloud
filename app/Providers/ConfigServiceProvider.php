<?php

namespace Ds\Providers;

use Ds\Services\ConfigService;
use Illuminate\Support\ServiceProvider;

class ConfigServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (defined('APP_LEVEL_ENABLED')) {
            return;
        }

        $this->app[ConfigService::class]->boot();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(ConfigService::class, function () {
            return ConfigService::getInstance();
        });
    }
}
