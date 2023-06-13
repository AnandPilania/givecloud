<?php

namespace Illuminate\A\Database;

use Ds\Illuminate\Database\Console\MigrateDataCommand;
use Ds\Illuminate\Database\Console\MigrateDataInstallCommand;
use Ds\Illuminate\Database\Console\MigrateDataMakeCommand;
use Ds\Illuminate\Database\MySqlConnection;
use Ds\Illuminate\Database\QueryListener;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Connection;
use Illuminate\Database\Migrations\DatabaseMigrationRepository;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\ServiceProvider;

class DatabaseServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('db.query-listener', function () {
            return new QueryListener;
        });

        $this->registerRepository();
        $this->registerMigrator();
        $this->registerCommands();
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        Connection::resolverFor('mysql', function ($connection, $database, $prefix, $config) {
            return new MySqlConnection($connection, $database, $prefix, $config);
        });
    }

    /**
     * Register the migration repository service.
     *
     * @return void
     */
    protected function registerRepository()
    {
        $this->app->singleton('migration.data.repository', function ($app) {
            $table = $app['config']['database.migrations'] . '_data';

            return new DatabaseMigrationRepository($app['db'], $table);
        });
    }

    /**
     * Register the migrator service.
     *
     * @return void
     */
    protected function registerMigrator()
    {
        // The migrator is responsible for actually running and rollback the migration
        // files in the application. We'll pass in our database connection resolver
        // so the migrator can resolve any of these connections when it needs to.
        $this->app->singleton('migrator.data', function ($app) {
            $repository = $app['migration.data.repository'];

            return new Migrator($repository, $app['db'], $app['files'], $app['events']);
        });
    }

    /**
     * Register the database commands.
     *
     * @return void
     */
    protected function registerCommands()
    {
        $this->app->singleton('command.migrate.data', function ($app) {
            return new MigrateDataCommand($app['migrator.data'], $app[Dispatcher::class]);
        });

        $this->app->singleton('command.migrate.install_data', function ($app) {
            return new MigrateDataInstallCommand($app['migration.data.repository']);
        });

        $this->app->singleton('command.migrate.make_data', function ($app) {
            return new MigrateDataMakeCommand($app['migration.creator'], $app['composer']);
        });

        $this->commands([
            'command.migrate.data',
            'command.migrate.install_data',
            'command.migrate.make_data',
        ]);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'db.query-listener',
            'migrator.data',
            'migration.data.repository',
            'command.migrate.data',
            'command.migrate.install_data',
            'command.migrate.make_data',
        ];
    }
}
