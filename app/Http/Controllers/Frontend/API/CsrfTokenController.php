<?php

namespace Ds\Http\Controllers\Frontend\API;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CsrfTokenController extends VerifyCsrfToken
{
    public function __invoke(Request $request): Response
    {
        if (! $this->tokensMatch($request)) {
            return response(['token' => csrf_token()]);
        }

        return response('', Response::HTTP_NO_CONTENT);
    }
}
