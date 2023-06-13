<?php

namespace Ds\Domain\Salesforce;

use Omniphx\Forrest\Authentications\WebServer;
use Omniphx\Forrest\Providers\Laravel\ForrestServiceProvider;

class SalesforceServiceProvider extends ForrestServiceProvider
{
    public function register(): void
    {
        parent::register();

        $this->app->extend('forrest', function (WebServer $forrest) {
            return tap($forrest, function (WebServer $forrest) {
                $forrest->setCredentials([
                    'consumerKey' => sys_get('salesforce_consumer_key'),
                    'consumerSecret' => sys_get('salesforce_consumer_secret'),
                    'loginURL' => config('database.connections.soql.loginURL'),
                    'callbackURI' => secure_site_url(route('backend.settings.integrations.salesforce.callback', [], false), true),
                ]);
            });
        });
    }

    protected function getStorage($storageType)
    {
        return app(SalesforceTokenStorage::class);
    }
}
