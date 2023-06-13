<?php

namespace Ds\Illuminate\Auth;

use Illuminate\Auth\SessionGuard;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class AccountSessionGuard extends SessionGuard
{
    /**
     * Get a unique identifier for the auth session value.
     *
     * @return string
     */
    public function getName()
    {
        return 'member_id';
    }

    /**
     * Get the name of the cookie used to store the "recaller".
     *
     * @return string
     */
    public function getRecallerName()
    {
        return member_remember_token_cookie_name();
    }

    /**
     * Queue the recaller cookie into the cookie jar.
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     * @return void
     */
    protected function queueRecallerCookie(AuthenticatableContract $user)
    {
        $this->getCookieJar()->queue($this->createRecaller(
            $user->email . '|' . $user->getRememberToken()
        ));
    }

    /**
     * Get the decrypted recaller cookie for the request.
     *
     * @return \Illuminate\Auth\Recaller|null
     */
    protected function recaller()
    {
        if (is_null($this->request)) {
            return;
        }

        if ($recaller = $this->request->cookies->get($this->getRecallerName())) {
            return new AccountRecaller($recaller);
        }
    }
}
