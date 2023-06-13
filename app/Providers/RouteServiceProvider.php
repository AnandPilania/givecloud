<?php

namespace Ds\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/jpanel';

    /**
     * The controller namespace for the application.
     *
     * When present, controller route declarations will automatically be prefixed with this namespace.
     *
     * @var string|null
     */
    protected $namespace = 'Ds\\Http\\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();
        $this->configureRouteBindings();

        $this->routes(function () {
            $this->mapApiRoutes();
            $this->mapWebRoutes();
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
        });
    }

    /**
     * Configure the route bindings for the application.
     *
     * @return void
     */
    protected function configureRouteBindings()
    {
        Route::pattern('id', '[\d]+');
        Route::pattern('name', '[\w-]+');
        Route::pattern('profile_id', '[\d\w-]+');

        Route::bind('cart', function ($value) {
            return \Ds\Models\Order::getActiveSession($value);
        });
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::group([
            'middleware' => 'api',
            'namespace' => $this->namespace,
        ], function ($router) {
            Route::get('static/{theme}/{path}', 'StylesheetController@asset')->where('path', '.+');
        });

        Route::prefix('jpanel')
            ->middleware(['web', 'track.visit'])
            ->namespace($this->namespace)
            ->group(base_path('routes/backend/web.php'));

        Route::middleware('web')
            ->namespace($this->namespace . '\\Frontend')
            ->group(base_path('routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('gc-json/v1')
            ->middleware('api')
            ->namespace($this->namespace . '\\Frontend\\API')
            ->group(base_path('routes/api.php'));

        Route::prefix('jpanel/api/v1')
            ->middleware('api')
            ->namespace($this->namespace)
            ->group(base_path('routes/backend/api.php'));

        Route::prefix('admin/api/v2')
            ->name('admin.api.v2.')
            ->middleware('api_v2')
            ->namespace($this->namespace . '\\API\\V2')
            ->group(base_path('routes/backend/api_v2.php'));

        Route::middleware('api')
            ->namespace($this->namespace . '\\API')
            ->group(function () {
                Route::get('shipstation.xml', 'ShipStationController@customStore');
                Route::post('shipstation.xml', 'ShipStationController@customStore');
            });

        Route::prefix('jpanel/api/v1')
            ->middleware(['api', 'auth'])
            ->group(base_path('routes/backend/feature_previews.php'));

        Route::middleware('api')
            ->namespace($this->namespace . '\\API')
            ->group(base_path('routes/backend/webhook.php'));

        Route::middleware([\Ds\Http\Middleware\ZapierEnabled::class, 'api_v2', 'scope:zapier'])
            ->prefix('zapier')
            ->namespace('Ds\\Domain\\Zapier\\Controllers')
            ->group(base_path('routes/backend/zapier.php'));

        Route::middleware('api')
            ->namespace('Ds\\Domain\\Flatfile\\Controllers')
            ->group(base_path('routes/backend/flatfile.php'));
    }
}
