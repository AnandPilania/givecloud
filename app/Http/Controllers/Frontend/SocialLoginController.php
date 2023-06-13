<?php

namespace Ds\Http\Controllers\Frontend;

use Ds\Domain\Shared\Exceptions\EmailNotProvidedException;
use Ds\Enums\SocialLogin\SupporterProviders;
use Ds\Http\Requests\Frontend\SupporterSocialLoginCallbackRequest;
use Ds\Mail\SocialLoginNewProviderDetected;
use Ds\Services\SupporterSocialLoginService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class SocialLoginController extends Controller
{
    public function redirect(string $provider, array $state = []): RedirectResponse
    {
        if (! in_array($provider, SupporterProviders::cases())) {
            abort(404);
        }

        $state = array_merge([
            'site' => sys_get('ds_account_name'),
            'provider' => $provider,
        ], $state);

        $driver = Socialite::driver($provider);

        if ($provider === 'facebook' && request()->reRequest) {
            $driver->reRequest();
        }

        return $driver->redirectUrl(config("services.$provider.supporter_redirect"))
            ->stateless()->with([
                'state' => base64_encode(json_encode($state)),
            ])->redirect();
    }

    public function transparent(string $provider): RedirectResponse
    {
        return $this->redirect($provider, ['transparent' => 'public']);
    }

    /**
     * @param \Ds\Http\Requests\Frontend\SupporterSocialLoginCallbackRequest $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function callback(SupporterSocialLoginCallbackRequest $request)
    {
        try {
            if (request('error') === 'access_denied') {
                return redirect(session()->pull('url.website_intended', secure_site_url(route('frontend.accounts.login', [], false))));
            }

            $member = app(SupporterSocialLoginService::class)->fromIncomingUser($request->provider);
            $socialIdentity = app(SupporterSocialLoginService::class)->findOrCreateSocialIdentity($request->provider);

            app(SupporterSocialLoginService::class)->login($member, $request->provider);

            $redirectTo = data_get(
                $member,
                'membership.default_url',
                route('frontend.accounts.home', [], false)
            );

            cart()->populateMember($member);

            // New connection for user, send notification
            if (! $member->wasRecentlyCreated && $socialIdentity->wasRecentlyCreated) {
                Mail::to($member)->send(new SocialLoginNewProviderDetected($member, $request->provider));
                session()->flash('liquid_req.success', __('frontend/accounts.profile.successfully_connected_account'));
            }

            $state = json_decode(base64_decode(request('state')));

            if ($state->transparent ?? false) {
                return view('auth.transparent', [
                    'origin' => $state->transparent === 'public' ? '*' : request()->getSchemeAndHttpHost(),
                    'message' => [
                        'type' => 'social_login',
                        'payload' => [
                            // limit exposure of personal/sensitive information since
                            // data will be broadly exposed when using '*' as the origin.
                            // instead return a status and require data lookup from session
                            // on the other side of the request
                            'successful' => true,
                        ],
                    ],
                ]);
            }

            return redirect(session()->pull('url.website_intended', secure_site_url($redirectTo)));
        } catch (EmailNotProvidedException $e) {
            if (session()->pull('social-login:email-not-provided')) {
                session()->flash('liquid_req.error', __('frontend/accounts.signup.email_not_provided'));

                app(SupporterSocialLoginService::class)->revokeConnection($request->provider);
            } elseif ($request->provider === 'facebook') {
                session()->put('social-login:email-not-provided', 1);

                return redirect()->action('Frontend\SocialLoginController@redirect', [
                    'provider' => $request->provider,
                    'reRequest' => true,
                ]);
            }
        } catch (Throwable $e) {
            report($e);
            session()->flash('liquid_req.error', __('frontend/accounts.profile.error'));
        }

        return redirect(secure_site_url(route('frontend.accounts.login', [], false)));
    }

    public function revoke(string $provider): RedirectResponse
    {
        /** @var \Ds\Models\Member $member */
        $member = member();
        $member->setVisible(['password']);

        if (is_null($member->password) && $member->socialIdentities()->count() === 1) {
            session()->flash('liquid_req.error', __('frontend/accounts.profile.unsuccessfull_revoked_connected_account'));

            return redirect()->route('accounts.profile');
        }

        app(SupporterSocialLoginService::class)->revoke($provider, member());

        session()->flash('liquid_req.success', __('frontend/accounts.profile.successfully_revoked_connected_account'));

        return redirect()->route('accounts.profile');
    }
}
