<?php

namespace Ds\Http\Middleware;

use Closure;
use Ds\Domain\MissionControl\Models\Site;
use Illuminate\Http\Request;
use Spatie\Url\Url;

class PrimaryDomain
{
    /**
     * The URIs that have exemptions.
     *
     * @var array
     */
    protected static $exemptions = [
        'carts/*',
        'jpanel',
        'jpanel/*',
        'wc-api/*',
        'webhook/*',
        'zapier/*',
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
        // Skip when redirect not possible due to request method
        if ($request->isMethod('GET') === false) {
            return $next($request);
        }

        // Skip when in migration mode
        if (sys_get('custom_domain_migration_mode')) {
            return $next($request);
        }

        // Skip if request has an exemption
        if (static::isExempt($request)) {
            return $next($request);
        }

        $site = site();

        // Skip when we are already on the primary domain
        if ($site->primary_domain === $request->getHost()) {
            return $next($request);
        }

        // Skip when primary domain can't do HTTPS and the request requires it
        if (! $site->primary_domain_ssl_enabled && ForceSSL::isProtected($request)) {
            return $next($request);
        }

        return $this->redirectToPrimaryDomain($site, $request);
    }

    /**
     * Check if the a request's URI has been exempt.
     *
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    public static function isExempt(Request $request)
    {
        foreach (static::$exemptions as $exempt) {
            if ($request->is($exempt)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Redirect to the primary domain.
     *
     * @param \Ds\Domain\MissionControl\Models\Site $site
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function redirectToPrimaryDomain(Site $site, Request $request)
    {
        $url = Url::fromString($request->fullUrl())
            ->withScheme($site->primary_domain_ssl_enabled ? 'https' : 'http')
            ->withHost($site->primary_domain)
            ->withPort(null);

        return redirect()->to((string) $url);
    }
}
