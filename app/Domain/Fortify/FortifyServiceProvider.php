<?php

namespace Ds\Domain\Fortify;

use Ds\Domain\Fortify\Actions\ResetUserPassword;
use Ds\Domain\Fortify\Actions\UpdateUserPassword;
use Ds\Domain\Fortify\Actions\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerResponseBindings();
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerActionBindings();
        $this->registerViewBindings();
        $this->registerRateLimiters();
    }

    protected function registerActionBindings(): void
    {
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        $this->app->singleton(
            \Laravel\Fortify\Actions\CompletePasswordReset::class,
            \Ds\Domain\Fortify\Actions\CompletePasswordReset::class
        );

        $this->app->singleton(
            \Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable::class,
            \Ds\Domain\Fortify\Actions\RedirectIfTwoFactorAuthenticatable::class
        );
    }

    protected function registerResponseBindings(): void
    {
        $this->app->singleton(
            \Laravel\Fortify\Contracts\LoginResponse::class,
            \Ds\Domain\Fortify\Responses\LoginResponse::class
        );
    }

    protected function registerViewBindings(): void
    {
        Fortify::loginView(function () {
            if (request('back')) {
                session()->put('url.intended', request('back'));
            }

            return view('auth.login');
        });

        Fortify::requestPasswordResetLinkView(function () {
            return view('auth.forgot-password');
        });

        Fortify::resetPasswordView(function ($request) {
            return view('auth.reset-password', ['request' => $request]);
        });

        // Fortify::confirmPasswordView(function () {
        //     return view('auth.confirm-password');
        // });

        Fortify::twoFactorChallengeView(function () {
            return view('auth.two-factor-challenge');
        });
    }

    protected function registerRateLimiters(): void
    {
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->email . $request->ip());
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }
}
