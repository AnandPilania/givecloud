<?php

namespace Ds\Http\Middleware;

use Closure;

class AuthorizationRateMonitoring
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
        if ($this->shouldReactivatePublicPayments()) {
            sys_set('public_payments_disabled', false);
            sys_set('public_payments_disabled_until', null);
        }

        return $next($request);
    }

    private function shouldReactivatePublicPayments(): bool
    {
        $paymentsDisabledUntil = sys_get('datetime:public_payments_disabled_until');

        return (bool) optional($paymentsDisabledUntil)->isPast();
    }
}
