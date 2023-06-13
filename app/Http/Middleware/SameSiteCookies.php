<?php

namespace Ds\Http\Middleware;

use Closure;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;
use UAParser\Parser;

class SameSiteCookies
{
    /** @var string */
    private $userAgentString;

    /** @var \UAParser\Result\Client */
    private $userAgent;

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

        $this->setUserAgent($request->userAgent());

        // Don’t send `SameSite=None` to known incompatible clients.
        // https://www.chromium.org/updates/same-site/incompatible-clients
        if ($this->isMissingSameSiteNoneSupport()) {
            $this->stripSameSiteNoneFromResponse($response);
        }

        return $response;
    }

    /**
     * Set and parse the user agent.
     *
     * @param string|null $userAgent
     */
    private function setUserAgent($userAgent)
    {
        $this->userAgentString = (string) $userAgent;
        $this->userAgent = Parser::create()->parse($this->userAgentString);
    }

    /**
     * Strip the SameSite flag from any response cookies using that
     * are using `SameSite=None`.
     *
     * @param \Symfony\Component\HttpFoundation\Response $response
     */
    private function stripSameSiteNoneFromResponse(Response $response)
    {
        foreach ($response->headers->getCookies() as $cookie) {
            if (strtolower($cookie->getSameSite()) !== 'none') {
                continue;
            }

            $response->headers->setCookie(new Cookie(
                $cookie->getName(),
                $cookie->getValue(),
                $cookie->getExpiresTime(),
                $cookie->getPath(),
                $cookie->getDomain(),
                $cookie->isSecure(),
                $cookie->isHttpOnly(),
                $cookie->isRaw(),
                null
            ));
        }
    }

    private function isMissingSameSiteNoneSupport(): bool
    {
        if (empty($this->userAgentString)) {
            return false;
        }

        return $this->hasWebKitSameSiteBug() || $this->dropsUnrecognizedSameSiteCookies();
    }

    private function hasWebKitSameSiteBug(): bool
    {
        return $this->isIosVersion(12) || ($this->isMacOsxVersion(10, 15) && $this->isSafari());
    }

    private function dropsUnrecognizedSameSiteCookies(): bool
    {
        return $this->isBuggyChrome() || $this->isBuggyUc();
    }

    private function isIosVersion(int $major): bool
    {
        return $this->userAgent->os->family === 'iOS' &&
            $this->userAgent->os->major == $major;
    }

    private function isMacOsxVersion(int $major, int $minor): bool
    {
        return $this->userAgent->os->family === 'Mac OS X' &&
            $this->userAgent->os->major == $major &&
            $this->userAgent->os->minor == $minor;
    }

    private function isSafari(): bool
    {
        return Str::contains($this->userAgent->ua->family, 'Safari');
    }

    private function isBuggyChrome(): bool
    {
        return $this->isChromiumBased() && $this->isChromeVersionBetween(51, 67);
    }

    private function isBuggyUc(): bool
    {
        return $this->isUcBrowser() && $this->isUcBrowserVersionAtLeast(12, 13, 2);
    }

    private function isChromiumBased(): bool
    {
        return preg_match('/Chrom(e|ium)/', $this->userAgentString) === 1;
    }

    private function isChromeVersionBetween(int $from, int $to): bool
    {
        preg_match('/Chrom[^\/]+\/(\d+)[\.\d]*/', $this->userAgentString, $matches);
        $version = (int) ($matches[1] ?? null);

        return $from <= $version && $version <= $to;
    }

    private function isUcBrowser(): bool
    {
        return $this->userAgent->ua->family === 'UC Browser';
    }

    private function isUcBrowserVersionAtLeast(int $major, int $minor, int $build): bool
    {
        if ((int) $this->userAgent->ua->major === $major) {
            if ((int) $this->userAgent->ua->minor === $minor) {
                return (int) $this->userAgent->ua->patch >= $build;
            }

            return (int) $this->userAgent->ua->minor > $minor;
        }

        return (int) $this->userAgent->ua->major > $major;
    }
}
