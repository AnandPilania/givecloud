<?php

namespace Ds\Providers;

use Ds\Illuminate\Auth\AccountSessionGuard;
use Ds\Illuminate\Auth\AuthTokenGuard;
use Ds\Illuminate\Auth\EloquentAccountProvider;
use Ds\Illuminate\Auth\EloquentUserProvider;
use Ds\Models\Passport\AuthCode;
use Ds\Models\Passport\Client;
use Ds\Models\Passport\Token;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'Ds\Models\Model' => 'Ds\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        $this->app['auth']->extend('account_session', function ($app, $name, array $config) {
            return $this->createAccountSessionDriver($name, $config);
        });

        $this->app['auth']->extend('auth_token', function ($app, $name, array $config) {
            return $this->createAuthTokenDriver($name, $config);
        });

        $this->app['auth']->provider('eloquent', function ($app, array $config) {
            return new EloquentUserProvider($app['hash'], $config['model']);
        });

        $this->app['auth']->provider('eloquent_account', function ($app, array $config) {
            return new EloquentAccountProvider($app['hash'], $config['model']);
        });

        $this->registerPassport();
    }

    /**
     * Create a session based authentication guard.
     *
     * @param string $name
     * @param array $config
     * @return \Ds\Illuminate\Auth\AccountSessionGuard
     */
    public function createAccountSessionDriver($name, $config)
    {
        $provider = $this->app['auth']->createUserProvider($config['provider'] ?? null);

        $guard = new AccountSessionGuard($name, $provider, $this->app['session.store']);

        // When using the remember me functionality of the authentication services we
        // will need to be set the encryption instance of the guard, which allows
        // secure, encrypted cookie values to get generated for those cookies.
        if (method_exists($guard, 'setCookieJar')) {
            $guard->setCookieJar($this->app['cookie']);
        }

        if (method_exists($guard, 'setDispatcher')) {
            $guard->setDispatcher($this->app['events']);
        }

        if (method_exists($guard, 'setRequest')) {
            $guard->setRequest($this->app->refresh('request', $guard, 'setRequest'));
        }

        return $guard;
    }

    /**
     * Create a token based authentication guard.
     *
     * @param string $name
     * @param array $config
     * @return \Ds\Illuminate\Auth\AuthTokenGuard
     */
    private function createAuthTokenDriver($name, array $config)
    {
        $guard = new AuthTokenGuard(
            $this->app['auth']->createUserProvider($config['provider'] ?? null),
            $this->app['request'],
            $config['input_key'] ?? 'api_token',
            $config['storage_key'] ?? 'api_token',
            $config['hash'] ?? false
        );

        $this->app->refresh('request', $guard, 'setRequest');

        return $guard;
    }

    private function registerPassport(): void
    {
        Passport::routes();

        Passport::tokensCan([
            'zapier' => 'Do Zapier actions',
        ]);

        Passport::useTokenModel(Token::class);
        Passport::useClientModel(Client::class);
        Passport::useAuthCodeModel(AuthCode::class);
    }
}
