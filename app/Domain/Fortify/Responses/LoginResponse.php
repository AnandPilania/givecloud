<?php

namespace Ds\Domain\Fortify\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        if (sys_get('onboarding_flow')) {
            return redirect()->to('jpanel/onboard/start');
        }

        if (sys_get('two_factor_authentication') === 'prompt' && ! is_super_user() && ! user()->two_factor_secret) {
            return redirect()->route('backend.auth.2fa_nagger');
        }

        return redirect()->intended(config('fortify.home'));
    }
}
