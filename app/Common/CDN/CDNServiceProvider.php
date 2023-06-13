<?php

namespace Ds\Common\CDN;

use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;
use Superbalist\Flysystem\GoogleStorage\GoogleStorageAdapter;

class CDNServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(Manager::class, function ($app) {
            $bucket = $this->app['cdn.client']->bucket(
                $app['config']->get('services.google-storage.cdn_bucket')
            );

            return new Manager($app, $bucket, site('cdn_path_prefix'));
        });

        $this->app->alias(Manager::class, 'cdn');

        $this->app->bind('cdn.client', function ($app) {
            return new StorageClient([
                'projectId' => $app['config']->get('services.google-storage.project_id'),
                'keyFilePath' => $app['config']->get('services.google-storage.key_file'),
            ]);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app['filesystem']->extend('google-cloud-storage', function ($app, $config) {
            return new Filesystem(new GoogleStorageAdapter(
                $app['cdn.client'],
                $app['cdn.client']->bucket($config['bucket']),
                Arr::get($config, 'root', site('cdn_path_prefix')),
                Arr::get($config, 'storage_api_uri')
            ));
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
            'cdn',
            'cdn.client',
        ];
    }
}
