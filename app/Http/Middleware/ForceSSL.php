<?php

namespace Ds\Http\Middleware;

use Closure;
use Ds\Domain\MissionControl\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Spatie\Url\Url;

class ForceSSL
{
    /**
     * The URIs that should be protected.
     *
     * @var array
     */
    protected static $protected = [
        'account',
        'account/*',
        'cart',
        'gc-json/*',
        'jpanel',
        'jpanel/*',
        'contributions',
        'contributions/*',
    ];

    /**
     * The URIs that have exemptions.
     *
     * @var array
     */
    protected static $exemptions = [
        '_assets/*',
        'jpanel/unlock_site',
    ];

    /**
     * The application environments which don't require SSL
     *
     * @var array
     */
    protected static $exemptEnv = [
        'local',
        // 'testing',
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
        // Skip if already secure
        if ($request->secure()) {
            return $next($request);
        }

        // Skip if request has an exemption
        if (static::isExempt($request)) {
            return $next($request);
        }

        $site = site();

        // Force for protected requests
        if (static::isProtected($request)) {
            return $this->redirectToHttps($site, $request);
        }

        // Force if possible and it's supported by the domain
        if ($site->isDomainSslEnabled($request->getHost())) {
            return $this->redirectToHttps($site, $request);
        }

        return $next($request);
    }

    /**
     * Check if the a request's URI has been exempt.
     *
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    public static function isExempt(Request $request)
    {
        // Exempt environments never require SSL. There should
        // only ever be development environments in the exemption list
        if (App::environment(static::$exemptEnv)) {
            return true;
        }

        foreach (static::$exemptions as $exempt) {
            if ($request->is($exempt)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the a request's URI is protected.
     *
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    public static function isProtected(Request $request)
    {
        foreach (static::$protected as $protected) {
            if ($request->is($protected)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Redirect to the HTTPS url for the site.
     *
     * @param \Ds\Domain\MissionControl\Models\Site $site
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function redirectToHttps(Site $site, Request $request)
    {
        $domain = $request->getHost();

        if (! $site->isDomainSslEnabled($domain)) {
            $domain = $site->secure_domain;
        }

        $url = Url::fromString($request->fullUrl())
            ->withScheme('https')
            ->withHost($domain)
            ->withPort(null);

        return redirect()->to((string) $url, 307);
    }
}
