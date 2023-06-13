<?php

namespace Ds\Http\Middleware;

use Closure;

class InjectSessionBeacon
{
    /** @var array */
    protected $whitelist = [
        \Symfony\Component\HttpFoundation\BinaryFileResponse::class,
        \Symfony\Component\HttpFoundation\JsonResponse::class,
        \Symfony\Component\HttpFoundation\RedirectResponse::class,
        \Symfony\Component\HttpFoundation\StreamedResponse::class,
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
        $response = $next($request);

        // skip whitelisted responses that don't need the beacon
        if ($this->isWhitelisted($response)) {
            return $response;
        }

        // ajax requests or requests that want JSON don't need the beacon
        if ($request->ajax() || $request->wantsJson()) {
            return $response;
        }

        $this->injectBeacon($request, $response);

        return $response;
    }

    /**
     * Check if the a response type has been whitelisted.
     *
     * @param \Symfony\Component\HttpFoundation\Response $response
     * @return bool
     */
    protected function isWhitelisted($response)
    {
        foreach ($this->whitelist as $whitelist) {
            if ($response instanceof $whitelist) {
                return true;
            }
        }

        return false;
    }

    /**
     * Inject the beacon into the response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Symfony\Component\HttpFoundation\Response $response
     */
    protected function injectBeacon($request, $response)
    {
        $domains = site()->domains
            ->where('ssl_enabled', true)
            ->pluck('name')
            ->merge(site()->subdomains);

        $markup = '';
        foreach ($domains as $domain) {
            if ($domain === $request->getHost()) {
                continue;
            }

            $markup .= sprintf(
                '<img src="//%s/cds-%s.gif" alt="" style="position:absolute;top:0;left:0;visibility:hidden;width:1px!important;height:1px!important;">' . PHP_EOL,
                $domain,
                session()->getId()
            );
        }

        if ($markup) {
            $response->setContent(str_replace(
                '</body>',
                "\n\n$markup\n</body>",
                $response->getContent()
            ));
        }
    }
}
