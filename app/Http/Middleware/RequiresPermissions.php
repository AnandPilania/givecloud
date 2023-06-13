<?php

namespace Ds\Http\Middleware;

use Closure;
use Ds\Providers\RouteServiceProvider;

class RequiresPermissions
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param string|array $permissions
     * @return mixed
     */
    public function handle($request, Closure $next, $permissions)
    {
        user()->canOrRedirect(
            explode(',', $permissions),
            url()->previous() ?: RouteServiceProvider::HOME,
            true,
        );

        return $next($request);
    }
}
