<?php

namespace Ds\Http\Controllers;

use Ds\Http\Requests\SocialLoginCallbackRequest;
use Ds\Services\SocialLoginService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class SocialiteController extends Controller
{
    public function redirect(string $provider): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        return Socialite::driver($provider)->stateless()->with([
            'state' => base64_encode(json_encode([
                'site' => sys_get('ds_account_name'),
                'provider' => $provider,
            ])),
        ])->redirect();
    }

    public function callback(SocialLoginCallbackRequest $request): RedirectResponse
    {
        try {
            if (app(SocialLoginService::class)->incomingUserIsConfirmed($request->provider)) {
                app(SocialLoginService::class)->loginIncomingUser($request->provider);

                return redirect()->intended(route('backend.session.index'));
            }

            $user = app(SocialLoginService::class)->fromIncomingUser($request->provider);

            app(SocialLoginService::class)->findOrCreateSocialIdentity($request->provider);
            app(SocialLoginService::class)->sendConfirmation($user, $request->provider);
        } catch (ModelNotFoundException $e) {
            $this->flash->error(
                sprintf(
                    'We couldn\'t find a user with the email provided : %s',
                    app(SocialLoginService::class)->incomingUser($request->provider)->getEmail()
                )
            );

            return redirect($this->route());
        } catch (Throwable $e) {
            $this->flash->error('An error occurred, please try again');

            return redirect($this->route());
        }

        $this->flash->success(
            sprintf(
                'To link your %s account, follow the confirmation link in the email that we sent to your address %s',
                ucfirst($request->provider),
                app(SocialLoginService::class)->incomingUser($request->provider)->getEmail()
            )
        );

        return redirect($this->route());
    }

    public function confirm(string $provider, string $token): RedirectResponse
    {
        if (! $user = app(SocialLoginService::class)->userFromToken($token)) {
            $this->flash->error('Cannot link account, please try again.');

            return redirect($this->route());
        }

        // Needs to be called before logging in the user.
        $route = Auth::check() ? $this->route() : route('backend.session.index');

        app(SocialLoginService::class)->confirmSocialIdentity($provider, $user);
        app(SocialLoginService::class)->login($user, $provider);

        $this->flash->success(ucfirst($provider) . ' account linked successfully.');

        return redirect($route);
    }

    public function revoke(string $provider, Authenticatable $user): RedirectResponse
    {
        app(SocialLoginService::class)->revoke($provider, $user);

        $this->flash->success('Successfully revoked access from ' . ucfirst($provider));

        return redirect($this->route());
    }

    protected function route(): string
    {
        if (Auth::check()) {
            return route('backend.profile');
        }

        return route('login');
    }
}
