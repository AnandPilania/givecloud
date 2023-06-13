<?php

namespace Ds\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param string[] ...$guards
     * @return mixed
     */
    public function handle($request, Closure $next, ...$guards)
    {
        if ($this->auth->guard('api')->check()) {
            $this->auth->setUser($this->auth->guard('api')->user());
        }

        $this->authenticate($request, $guards);

        if ($this->forceTwoFactorAuthenticationSetup($request)) {
            return redirect()->route('backend.auth.2fa_nagger');
        }

        return $next($request);
    }

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param \Illuminate\Http\Request $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            return route('login');
        }
    }

    private function forceTwoFactorAuthenticationSetup(Request $request): bool
    {
        if (is_super_user() || user()->two_factor_secret) {
            return false;
        }

        if (sys_get('two_factor_authentication') !== 'force') {
            return false;
        }

        if ($request->is('jpanel/auth/2fa-nagger', 'jpanel/auth/user/*')) {
            return false;
        }

        return true;
    }
}
