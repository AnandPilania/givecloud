<?php

namespace Ds\Illuminate\Routing;

/** @mixin \Illuminate\Routing\UrlGenerator */
class UrlGeneratorMixin
{
    /**
     * Get the shortlink for a URL.
     */
    public function shortlink()
    {
        return function ($url) {
            return shortlink($url);
        };
    }

    /**
     * Get the shortlink for a named route.
     */
    public function routeAsShortlink()
    {
        return function ($name, $parameters = [], $absolute = true) {
            $url = $this->route($name, $parameters, $absolute);

            $routeName = trim($name . ':' . implode(':', $this->formatParameters($parameters)), ':');

            return shortlink($url, $routeName);
        };
    }
}
