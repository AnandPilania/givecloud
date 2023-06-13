<?php

namespace Ds\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * @var string[]
     */
    protected $middleware = [
        \Ds\Http\Middleware\Activitron::class,
        // \Ds\Http\Middleware\TrustHosts::class,
        \Ds\Http\Middleware\TrustProxies::class,
        \Fruitcake\Cors\HandleCors::class,
        \Ds\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \Ds\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        \Ds\Http\Middleware\EncryptCookies::class,
        \Ds\Http\Middleware\SameSiteCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,
        // \Illuminate\Session\Middleware\AuthenticateSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \Ds\Http\Middleware\VerifyCsrfToken::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \Ds\Http\Middleware\SetLocale::class,
            \Ds\Http\Middleware\PrimaryDomain::class,
            \Ds\Http\Middleware\ForceSSL::class,
            \Ds\Http\Middleware\AuthorizationRateMonitoring::class,
            \Ds\Http\Middleware\SuspendedSite::class,
            \Ds\Http\Middleware\SiteLockScreen::class,
            \Ds\Http\Middleware\InjectSessionBeacon::class,
            \Ds\Http\Middleware\CheckReferral::class,
            \Ds\Http\Middleware\BillingWarning::class,
        ],
        'api' => [
            // 'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \Ds\Http\Middleware\SetLocale::class,
        ],
        'api_v2' => [
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            'auth:passport',
        ],
    ];

    /**
     * The application's route middleware.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => \Ds\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'auth.member' => \Ds\Http\Middleware\AuthenticateMember::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'client' => \Laravel\Passport\Http\Middleware\CheckClientCredentials::class,
        'guest' => \Ds\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'requires.feature' => \Ds\Http\Middleware\RequiresFeature::class,
        'requires.permissions' => \Ds\Http\Middleware\RequiresPermissions::class,
        'requires.superUser' => \Ds\Http\Middleware\RequiresSuperUser::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'track.visit' => \Ds\Http\Middleware\TrackPageVisit::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        'scopes' => \Laravel\Passport\Http\Middleware\CheckScopes::class,
        'scope' => \Laravel\Passport\Http\Middleware\CheckForAnyScope::class,
    ];

    /**
     * The priority-sorted list of middleware.
     *
     * Forces the listed middleware to always be in the given order.
     *
     * @var array
     */
    protected $middlewarePriority = [
        \Ds\Http\Middleware\Activitron::class,
        \Ds\Http\Middleware\PrimaryDomain::class,
        \Ds\Http\Middleware\ForceSSL::class,
        \Ds\Http\Middleware\CheckReferral::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \Ds\Http\Middleware\Authenticate::class,
        \Illuminate\Routing\Middleware\ThrottleRequests::class,
        \Illuminate\Session\Middleware\AuthenticateSession::class,
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
        \Illuminate\Auth\Middleware\Authorize::class,
    ];

    /**
     * Handle an incoming HTTP request.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function handle($request)
    {
        $excludeCookieMiddleware = [
            'static/*/*',
        ];

        if ($request->is(...$excludeCookieMiddleware)) {
            $middlewares = [
                \Ds\Http\Middleware\VerifyCsrfToken::class,
                \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
                \Illuminate\Session\Middleware\StartSession::class,
                \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            ];

            foreach ($middlewares as $middleware) {
                unset($this->middleware[array_search($middleware, $this->middleware)]);
            }

            $this->middleware = array_values($this->middleware);
        }

        return parent::handle($request);
    }
}
