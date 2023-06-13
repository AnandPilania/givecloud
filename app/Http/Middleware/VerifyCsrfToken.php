<?php

namespace Ds\Http\Middleware;

use Closure;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        'carts/*/tokenize/*',
        'flatfile/*',
        'gc-json/v1/carts/*/capture',
        'gc-json/v1/carts/*/charge',
        'gc-json/v1/collect',
        'jpanel/api/v1/auth/login',
        'oauth/token',
        'shipstation.xml',
        'wc-api/WC_Gateway_Paypal',
        'webhook/*',
        'zapier/*',

        // temporary, coming back to address
        // after fundraising forms launch
        'gc-json/v1/checkouts',
        'gc-json/v1/carts/*/*',
    ];

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     *
     * @throws \Illuminate\Session\TokenMismatchException
     */
    public function handle($request, Closure $next)
    {
        // Ignore CSRF for requests using bearer authorization
        if ($request->bearerToken()) {
            return $next($request);
        }

        return parent::handle($request, $next);
    }
}
