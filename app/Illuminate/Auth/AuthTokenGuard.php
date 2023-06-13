<?php

namespace Ds\Illuminate\Auth;

use Illuminate\Auth\TokenGuard;

class AuthTokenGuard extends TokenGuard
{
    /**
     * Get the token for the current request.
     *
     * @return string
     */
    public function getTokenForRequest()
    {
        $token = parent::getTokenForRequest();

        if (empty($token)) {
            $token = $this->request->header('X-Auth-Token', '');
        }

        return $token;
    }
}
