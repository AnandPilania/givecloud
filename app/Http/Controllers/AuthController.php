<?php

namespace Ds\Http\Controllers;

use Ds\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function twoFactorNagger()
    {
        $user = Auth::user();

        if ($user->two_factor_secret) {
            return redirect(RouteServiceProvider::HOME);
        }

        return view('auth.two-factor-nagger', [
            'user' => $user,
            'redirect_to' => url(RouteServiceProvider::HOME),
        ]);
    }
}
