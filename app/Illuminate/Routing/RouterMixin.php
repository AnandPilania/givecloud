<?php

namespace Ds\Illuminate\Routing;

/** @mixin \Illuminate\Routing\Router */
class RouterMixin
{
    /**
     * Create a permanent redirect from one URI to another including the original query string.
     */
    public function permanentRedirectWithQueryString()
    {
        return function ($uri, $destination) {
            return $this->any($uri, '\Ds\Illuminate\Routing\RedirectWithQueryStringController')
                ->defaults('destination', $destination)
                ->defaults('status', 302);
        };
    }
}
