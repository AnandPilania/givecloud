<?php

namespace Ds\Illuminate\Http;

use Spatie\Url\QueryParameterBag;
use Spatie\Url\Url;

/**
 * @property \Illuminate\Http\Request $request;
 *
 * @mixin \Illuminate\Http\RedirectResponse
 */
class RedirectResponseMixin
{
    /**
     * Create a new redirect response to the previously intended location.
     */
    public function withQueryString()
    {
        return function (array $input = []) {
            $query = empty($input) && $this->request
                ? $this->request->getQueryString()
                : (string) new QueryParameterBag($input);

            return $this->setTargetUrl(
                Url::fromString($this->getTargetUrl())->withQuery($query)
            );
        };
    }
}
