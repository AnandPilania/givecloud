<?php

namespace Ds\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ZapierEnabled
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (! sys_get('zapier_enabled', false)) {
            return response()->json('Unauthorized.', Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
