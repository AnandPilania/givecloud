<?php

namespace Ds\Http\Controllers\Frontend;

use Ds\Models\AutologinToken;
use Ds\Repositories\AccountTypeRepository;
use Illuminate\Support\Facades\DB;

class AccountsController extends Controller
{
    /**
     * Register controller middleware.
     */
    protected function registerMiddleware()
    {
        $this->middleware('auth.member', ['only' => [
            'home',
            'profile',
            'change_password',
            'logout',
            'orders',
        ]]);
    }

    public function register(AccountTypeRepository $accountTypeRepository)
    {
        pageSetup(__('frontend/accounts.register.create_account'));

        return $this->renderTemplate('accounts/register', [
            'account_types' => $accountTypeRepository->getOnWebAccountTypeDrops(),
            'recaptcha' => app('recaptcha')->getHtml(),
        ]);
    }

    public function reset_password(AccountTypeRepository $accountTypeRepository)
    {
        pageSetup(__('frontend/accounts.reset_password.reset_password'));

        return $this->renderTemplate('accounts/reset-password', [
            'notice' => null,
            'account_types' => $accountTypeRepository->getOnWebAccountTypeDrops(),
        ]);
    }

    public function home()
    {
        pageSetup(__('frontend/accounts.home.my_account'));

        return $this->renderTemplate('accounts/home');
    }

    public function change_password()
    {
        pageSetup(__('frontend/accounts.change_password.my_account'));

        return $this->renderTemplate('accounts/change-password');
    }

    public function profile(AccountTypeRepository $accountTypeRepository)
    {
        pageSetup(__('frontend/accounts.profile.my_profile'));

        // make sure a member is logged in
        if (! member_is_logged_in()) {
            return redirect()->to('/');
        }

        return $this->renderTemplate('accounts/profile', [
            'member' => member(),
            'regions' => [
                'CA' => DB::select("SELECT code, name FROM region WHERE country = 'CA' ORDER BY country DESC, code"),
                'US' => DB::select("SELECT code, name FROM region WHERE country = 'US' ORDER BY country DESC, code"),
            ],
            'countries' => cart_countries(),
            'notice' => request('success') ? 'save_success' : (request('fail') ? 'save_fail' : null),
            'account_types' => $accountTypeRepository->getOnWebAccountTypeDrops(),
            'groups' => \Ds\Models\Membership::availableInProfile()->get(),
            'marketing_optout_reason_required' => sys_get('marketing_optout_reason_required'),
            'marketing_optout_options' => explode(',', sys_get('marketing_optout_options')),
            'marketing_optout_other' => sys_get('marketing_optout_other'),
        ]);
    }

    public function login(AccountTypeRepository $accountTypeRepository)
    {
        pageSetup(__('frontend/accounts.login.login'));

        if (request('back')) {
            session()->put(
                'url.website_intended',
                secure_site_url(request('back'))
            );
        }

        // template data
        return $this->renderTemplate('accounts/login', [
            'login' => (object) [
                'failed' => request('failed'),
            ],
            'recaptcha' => app('recaptcha')->getHtml(),
            'notice' => request('fail') ? 'signup_fail' : '',
            'account_types' => $accountTypeRepository->getOnWebAccountTypeDrops(),
        ]);
    }

    public function logout()
    {
        member_logout();

        return redirect()->back();
    }

    public function signup()
    {
        pageSetup(__('frontend/accounts.signup.sign_up'));

        return $this->renderTemplate('accounts/signup', [
            'regions' => [
                'CA' => DB::select("SELECT code, name FROM region WHERE country = 'CA' ORDER BY country DESC, code"),
                'US' => DB::select("SELECT code, name FROM region WHERE country = 'US' ORDER BY country DESC, code"),
            ],
            'countries' => cart_countries(),
            'account_types' => \Ds\Models\AccountType::all(),
            'recaptcha' => app('recaptcha')->getHtml(),
        ]);
    }

    public function orders()
    {
        pageSetup(__('frontend/accounts.orders.my_history'));

        return $this->renderTemplate('accounts/orders', [
            'orders' => member()->orders()->paid()->get(),
            'gifts' => dpo_get_gift_history_for_donor(member('donor_id')),
        ]);
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function autologin($token)
    {
        $token = AutologinToken::token($token)->active()->firstOrFail();
        $token->consumeToken();

        return redirect()->to($token->path ?: $token->user->getAutologinDefaultUrl());
    }
}
