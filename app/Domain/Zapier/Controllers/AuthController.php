<?php

namespace Ds\Domain\Zapier\Controllers;

use Illuminate\Http\JsonResponse;

class AuthController
{
    /**
     * Zapier needs an url to check that the authentication works,
     * Therefore this method simply returns 200.
     */
    public function show(): JsonResponse
    {
        return response()->json();
    }
}
