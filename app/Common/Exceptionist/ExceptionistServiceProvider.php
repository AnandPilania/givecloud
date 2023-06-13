<?php

namespace Ds\Common\Exceptionist;

use Bugsnag\BugsnagLaravel\BugsnagServiceProvider;
use Illuminate\Support\ServiceProvider;

class ExceptionistServiceProvider extends ServiceProvider
{
    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->bound('bugsnag')) {
            $this->app['bugsnag']->setStripPath(dirname(base_path()));
            $this->app['bugsnag']->registerCallback([$this->app['exceptionist'], 'includeMetaData']);

            if (app()->environment('local', 'testing')) {
                $this->app['bugsnag']->setNotifyReleaseStages(['(none)']);
            }
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('exceptionist', function ($app) {
            return new Manager($app);
        });

        $this->app->register(BugsnagServiceProvider::class);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'exceptionist',
        ];
    }
}
