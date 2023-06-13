<?php

namespace Ds\Illuminate\Routing;

/**
 * @property \Illuminate\Routing\UrlGenerator $generator;
 * @property \Illuminate\Session\Store $session;
 *
 * @mixin \Illuminate\Routing\Redirector
 */
class RedirectorMixin
{
    /**
     * Create a new redirect response, while putting the current URL in the session.
     */
    public function websiteGuest()
    {
        return function ($path, $status = 302, $headers = [], $secure = null) {
            $request = $this->generator->getRequest();

            $intended = $request->method() === 'GET' && $request->route() && ! $request->expectsJson()
                            ? $this->generator->full()
                            : $this->generator->previous();

            if ($intended) {
                $this->session->put('url.website_intended', $intended);
            }

            return $this->to($path, $status, $headers, $secure);
        };
    }

    /**
     * Create a new redirect response to the previously intended location.
     */
    public function websiteIntended()
    {
        return function ($default = '/', $status = 302, $headers = [], $secure = null) {
            $path = $this->session->pull('url.website_intended', $default);

            return $this->to($path, $status, $headers, $secure);
        };
    }
}
