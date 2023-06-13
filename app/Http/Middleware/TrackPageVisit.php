<?php

namespace Ds\Http\Middleware;

use Closure;
use Ds\Models\UserPageVisit;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TrackPageVisit
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (! $request->isMethod('GET')) {
            return $next($request);
        }

        if (! session('user_login_id')) {
            return $next($request);
        }

        if (! Str::startsWith($request->getRequestUri(), '/jpanel')) {
            return $next($request);
        }

        UserPageVisit::create([
            'url' => $request->getRequestUri(),
            'user_id' => user('id'),
            'user_login_id' => session('user_login_id'),
        ]);

        return $next($request);
    }
}
