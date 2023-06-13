<?php

namespace Ds\Illuminate\Routing;

use Illuminate\Http\Request;
use Illuminate\Routing\RedirectController;
use Illuminate\Routing\UrlGenerator;

class RedirectWithQueryStringController extends RedirectController
{
    /**
     * Invoke the controller method.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Routing\UrlGenerator $url
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(Request $request, UrlGenerator $url)
    {
        return parent::__invoke($request, $url)->withQueryString($request->input());
    }
}
