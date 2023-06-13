<?php

namespace Ds\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SiteLockScreen
{
    /** @var array */
    protected $uriExemptions = [
        'a/*',
        'carts/*',
        'gc-json/*',
        'jpanel',
        'jpanel/*',
        'webhook/*',
    ];

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (! $this->showLockScreen($request)) {
            return $next($request);
        }

        // push intended URL into session
        redirect()->websiteGuest('/');

        return view('auth.lock-screen');
    }

    protected function showLockScreen(Request $request): bool
    {
        if ($request->is($this->uriExemptions)) {
            return false;
        }

        return site()->isLocked();
    }
}
