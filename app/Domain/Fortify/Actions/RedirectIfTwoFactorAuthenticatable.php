<?php

namespace Ds\Domain\Fortify\Actions;

use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable as FortifyRedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Fortify;

class RedirectIfTwoFactorAuthenticatable extends FortifyRedirectIfTwoFactorAuthenticatable
{
    /**
     * Attempt to validate the incoming credentials.
     *
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    protected function validateCredentials($request)
    {
        if (! method_exists($this->guard, 'validate')) {
            return parent::validateCredentials($request);
        }

        $credentials = $request->only(Fortify::username(), 'password');

        if ($this->guard->validate($credentials)) {
            $model = $this->guard->getProvider()->getModel();

            return $model::where(Fortify::username(), $request->{Fortify::username()})->first();
        }

        return null;
    }
}
