<?php

namespace Ds\Http\Controllers;

use Ds\Models\User;
use Ds\Repositories\AdminSidebarMenuRepository;
use Ds\Services\PersonalAccessTokenService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;

class UserController extends Controller
{
    /** @var \Ds\Services\PersonalAccessTokenService */
    private $personalAccessTokenService;

    /** @var \Ds\Repositories\AdminSidebarMenuRepository */
    private $adminSidebarMenuRepository;

    public function __construct(
        PersonalAccessTokenService $personalAccessTokenService,
        AdminSidebarMenuRepository $adminSidebarMenuRepository
    ) {
        parent::__construct();

        $this->personalAccessTokenService = $personalAccessTokenService;
        $this->adminSidebarMenuRepository = $adminSidebarMenuRepository;
    }

    public function destroy()
    {
        user()->canOrRedirect('user.edit');

        $user = User::notSuperUser()->findOrFail(request('id'));

        // soft delete user
        $user->deleted_at = now();
        $user->deleted_by = user('id');
        $user->save();

        $this->flash->success($user->full_name . ' deleted successfully.');

        return redirect()->to('jpanel/users');
    }

    public function emails()
    {
        user()->canOrRedirect('user');

        pageSetup('Export Emails');

        $qList = db_query("SELECT DISTINCT CONCAT(u.firstname,' ',u.lastname,' &lt;',u.email,'&gt;') AS email_formatted, email FROM `user` u WHERE u.isadminuser = 0 ORDER BY lastname, firstname");

        return $this->getView('users/emails', compact('qList'));
    }

    public function index()
    {
        user()->canOrRedirect('user');

        pageSetup('Users', 'jpanel');

        return view('users.index', [
            '__menu' => 'admin.users',
            'users' => User::active()->get(),
        ]);
    }

    public function insert()
    {
        user()->canOrRedirect('user.add');

        // prevent email duplication
        if (! User::validateUsername(request('email'))) {
            $this->flash->error("The email '" . request('email') . "' is already in use. Please use a different email address.");

            return redirect()->to('jpanel/users/edit');
        }

        if (request()->filled('password')) {
            $validator = app('validator')->make(
                request()->all(),
                ['password' => 'required|string|min:7|regex:/^(?=.*?[A-Za-z])(?=.*?[0-9]).{7,}$/'],
                ['password.regex' => 'Password must contain a combination of letters and numbers.']
            );

            if ($validator->fails()) {
                $this->flash->error($validator->errors()->first());

                return redirect()->back()->withInput();
            }
        }

        // save user
        $user = \Ds\Models\User::newWithPermission();
        $user->firstname = request('firstName');
        $user->lastname = request('lastName');
        $user->email = request('email');
        $user->hashed_password = Hash::make(request('password'));
        $user->primaryphonenumber = request('primaryPhoneNumber');
        $user->alternatephonenumber = request('alternatePhoneNumber');
        $user->permissions_json = request('permissions_json');
        $user->isadminuser = true;
        $user->is_account_admin = (request()->input('is_account_admin') == 1);
        $user->ds_corporate_optin = (request()->input('ds_corporate_optin') == 1);
        $user->notify_recurring_batch_summary = request()->has('notify_recurring_batch_summary');
        $user->save();

        $this->flash->success($user->full_name . ' created successfully.');

        return redirect()->to('jpanel/users');
    }

    public function update()
    {
        user()->canOrRedirect('user.edit');

        // prevent email duplication
        if (! User::validateUsername(request('email'), request('id'))) {
            $this->flash->error("The email '" . request('email') . "' is already in use. Please use a different email address.");

            return redirect()->to('jpanel/users/edit?i=' . request('id'));
        }

        // save user
        $user = \Ds\Models\User::notSuperUser()->findOrFail(request('id'));
        $user->firstname = request('firstName');
        $user->lastname = request('lastName');
        $user->email = request('email');
        $user->primaryphonenumber = request('primaryPhoneNumber');
        $user->alternatephonenumber = request('alternatePhoneNumber');
        $user->is_account_admin = (request()->input('is_account_admin', $user->is_account_admin) == 1);
        $user->ds_corporate_optin = (request()->input('ds_corporate_optin') == 1);
        $user->notify_recurring_batch_summary = request()->has('notify_recurring_batch_summary');
        $user->permissions_json = request('permissions_json');
        $user->save();

        // redirect
        $this->flash->success($user->full_name . ' updated successfully.');

        return redirect()->to('jpanel/users/edit?i=' . $user->id);
    }

    public function view()
    {
        user()->canOrRedirect('user');

        $__menu = 'admin.users';

        if (request('i')) {
            $user = \Ds\Models\User::notSuperUser()->findOrFail(request('i'));
            $title = $user->full_name;
            $action = '/jpanel/users/update';
        } else {
            $user = new \Ds\Models\User;
            $title = 'Add User';
            $action = '/jpanel/users/insert';
        }

        $isNew = $user->exists;

        pageSetup($title, 'jpanel');

        return $this->getView('users/view', compact('__menu', 'user', 'title', 'action', 'isNew'));
    }

    public function profile()
    {
        pageSetup('My Profile', 'jpanel');

        return view('users.profile', [
            'user' => user(),
            'connectedAccounts' => user()->socialIdentities()->confirmed()->get()->keyBy('provider_name'),
            'personalAccessTokens' => $this->personalAccessTokenService->getAllForUser(auth()->id()),
            'menuItems' => $this->adminSidebarMenuRepository->flat(),
            'pinnedItems' => user()->metadata('pinned-menu-items', [
                'features_website_view_site',
                'contributions',
            ]),
        ]);
    }

    public function notifications()
    {
        $user = user();
        $user->ds_corporate_optin = (bool) request('email_updates_optin');
        $user->notify_digest_daily = (bool) request('notify_digest_daily');
        $user->notify_digest_weekly = (bool) request('notify_digest_weekly');
        $user->notify_digest_monthly = (bool) request('notify_digest_monthly');
        $user->save();

        $this->flash->success('Your profile has been updated successfully.');

        return redirect()->back();
    }

    /**
     * Disable Two Factor Authentication for the given user.
     */
    public function disableTwoFactorAuthentication(DisableTwoFactorAuthentication $disable, $id): RedirectResponse
    {
        user()->canOrRedirect('user.add');

        $user = User::notSuperUser()->findOrFail($id);

        $disable($user);

        $this->flash->success('Two Factor Authentication disabled for user.');

        return redirect()->back();
    }

    /**
     * Regenerate the users API token.
     *
     * @param int $id
     * @return array
     */
    public function regenerateKey($id)
    {
        user()->canOrRedirect('user.add');

        $user = \Ds\Models\User::findOrFail($id);
        $user->api_token = Str::random(60);
        $user->save();

        return ['api_token' => $user->api_token];
    }

    /**
     * Send a reset link to the given user.
     *
     * @param int $user_id
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendResetLinkEmail($user_id, Request $request)
    {
        $user = User::find($user_id);

        if (empty($user->email)) {
            $this->flash->error(trans('auth.passwords.email'));

            return redirect()->back();
        }

        $response = Password::broker()->sendResetLink(['email' => $user->email]);

        if ($response === Password::RESET_LINK_SENT) {
            $this->flash->success(trans($response));

            return redirect()->back();
        }

        $this->flash->error(trans($response));

        return redirect()->back();
    }
}
