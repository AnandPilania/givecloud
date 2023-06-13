<?php

namespace Ds\Illuminate\Queue;

use Ds\Illuminate\Queue\Connectors\DatabaseConnector;
use Ds\Illuminate\Queue\Connectors\RedisConnector;
use Ds\Illuminate\Queue\Connectors\SyncConnector;
use Illuminate\Queue\Failed\NullFailedJobProvider;
use Illuminate\Support\ServiceProvider;

class QueueServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app['queue']->addConnector('database', function () {
            return new DatabaseConnector($this->app['db']);
        });

        $this->app['queue']->addConnector('redis', function () {
            return new RedisConnector($this->app['redis']);
        });

        $this->app['queue']->addConnector('sync', fn () => new SyncConnector);

        $this->app->singleton('queue.failer', function ($app) {
            $config = $app['config']['queue.failed'];

            if (isset($config['table'])) {
                return new DatabaseFailedJobProvider($app['db'], $config['database'], $config['table']);
            }

            return new NullFailedJobProvider;
        });
    }
}
