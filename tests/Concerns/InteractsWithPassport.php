<?php

namespace Tests\Concerns;

use Illuminate\Foundation\Application;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;

trait InteractsWithPassport
{
    public static $personalAccessClient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setupGlobalPersonalAccessClient($this->app);
    }

    private function setupGlobalPersonalAccessClient(Application $app): void
    {
        if (! self::$personalAccessClient) {
            // Clean all MC tables leftovers from previous run.
            Passport::client()->truncate();
            Passport::token()->truncate();
            Passport::personalAccessClient()->truncate();
            Passport::refreshToken()->truncate();

            self::$personalAccessClient = $app->make(ClientRepository::class)->createPersonalAccessClient(
                null,
                'Givecloud Personal Access Client',
                'http://localhost'
            );
        }

        // These sys_set() keys are then booted into passport.personal_access_client.*
        // to be used correctly by Passport from its configuration.
        sys_set('passport_personal_access_client_id', self::$personalAccessClient->id);
        sys_set('passport_personal_access_client_secret', self::$personalAccessClient->plainSecret);

        // We need to "refresh" ClientRepository singleton
        // as it was created without client id and secret.
        $app->singleton(ClientRepository::class, function ($container) {
            return new ClientRepository(
                sys_get('passport_personal_access_client_id'),
                sys_get('passport_personal_access_client_secret')
            );
        });
    }
}
