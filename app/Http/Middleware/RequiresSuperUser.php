<?php

namespace Ds\Http\Middleware;

use Closure;

class RequiresSuperUser
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (! is_super_user()) {
            app('flash')->error('This feature is restricted.');

            return redirect()->to('/jpanel');
        }

        return $next($request);
    }
}
