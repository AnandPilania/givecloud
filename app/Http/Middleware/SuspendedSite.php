<?php

namespace Ds\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SuspendedSite
{
    /** @var array */
    protected $uriExemptions = [
        'a/*', // required to allow CX to login from MC
        'jpanel/auth*', // allow them to access the auth routes
        'jpanel/settings/billing*', // allow them to access the billing settings page
        'jpanel/socialite*', // allow them to access the socialite login routes
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
        if (! $this->showSuspendedScreen($request)) {
            return $next($request);
        }

        return view('auth.suspended');
    }

    protected function showSuspendedScreen(Request $request): bool
    {
        if (is_super_user()) {
            return false;
        }

        if ($request->is($this->uriExemptions)) {
            return false;
        }

        if (site()->isTrial() && site()->trialHasExpired()) {
            return true;
        }

        return site()->isSuspended();
    }
}
