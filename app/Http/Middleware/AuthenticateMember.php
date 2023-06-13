<?php

namespace Ds\Http\Middleware;

use Closure;

class AuthenticateMember
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param string|null $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (! member_is_logged_in() && ! member_login_with_token()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response('Unauthorized.', 401);
            }

            return redirect()->websiteGuest('account/login');
        }

        if (member('force_password_reset') && ! $request->is(
            'account/logout',
            'account/change-password',
            'gc-json/v1/account/change-password'
        )) {
            if ($request->ajax() || $request->wantsJson()) {
                return response('Unauthorized.', 401);
            }

            return redirect()->websiteGuest('account/change-password');
        }

        return $next($request);
    }
}
