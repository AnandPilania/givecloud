<?php

namespace Ds\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetLocale
{
    /** @var array */
    private $locales = [
        'en-US',
        'es-MX',
        'fr-CA',
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
        if (! is_jpanel_route()) {
            app()->setLocale($this->getLocale($request));
        }

        return $next($request);
    }

    private function getLocale(Request $request): string
    {
        if (in_array($request->header('x-locale'), $this->locales, true)) {
            return $request->header('x-locale');
        }

        return sys_get('locale') ?: config('app.fallback_locale');
    }
}
