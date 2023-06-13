<?php

namespace Ds\Http\Controllers\Frontend\API\Account;

use Ds\Domain\Theming\Liquid\Drop;
use Ds\Http\Controllers\Frontend\API\Controller;
use Ds\Models\AccountType;
use Ds\Models\Email;
use Ds\Models\Member;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;
use Throwable;

class AuthController extends Controller
{
    /**
     * Register controller middleware.
     */
    protected function registerMiddleware()
    {
        $this->middleware('auth.member', ['only' => [
            'accountLogout',
            'changePassword',
        ]]);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function accountLogin()
    {
        $member = member_login(request('email'), request('password'), request('remember_me'));

        if ($member) {
            $redirectTo = data_get(
                $member,
                'membership.default_url',
                'account/home'
            );

            $member = member();

            if (request('verify_sms')) {
                $data = ['password' => $member->password];

                if ($res = $this->verifySms($data, true)) {
                    return $res;
                }

                $member->update($data);
            }

            cart()->populateMember($member);

            return $this->success([
                'account' => Drop::factory($member, 'Account'),
                'redirect_to' => session()->pull('url.website_intended', secure_site_url($redirectTo)),
            ]);
        }

        if (auth()->validate(['email' => request('email'), 'password' => request('password')])) {
            return $this->failure('<span>' . __('frontend/api.incorrect_login_with_jpanel_link', ['jpanel_url' => '/jpanel']) . '</span>', 403);
        }

        return $this->failure(__('frontend/api.incorrect_login'), 403);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function accountLogout()
    {
        member_logout();

        return $this->success([
            'account' => null,
        ]);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function signupProcess()
    {
        $data = [
            'title' => request('title'),
            'first_name' => request('first_name'),
            'last_name' => request('last_name'),
            'email' => request('email'),
            'password' => request('password'),
            'bill_title' => request('title'),
            'bill_first_name' => request('first_name'),
            'bill_last_name' => request('last_name'),
            'bill_organization_name' => request('organization_name'),
            'bill_email' => request('email'),
            'bill_address_01' => request('address1'),
            'bill_address_02' => request('address2'),
            'bill_city' => request('city'),
            'bill_state' => request('state'),
            'bill_country' => request('country'),
            'bill_zip' => request('zip'),
            'bill_phone' => request('phone'),
            'ship_title' => request('title'),
            'ship_first_name' => request('first_name'),
            'ship_last_name' => request('last_name'),
            'ship_organization_name' => request('organization_name'),
            'ship_email' => request('email'),
            'ship_address_01' => request('address1'),
            'ship_address_02' => request('address2'),
            'ship_city' => request('city'),
            'ship_state' => request('state'),
            'ship_country' => request('country'),
            'ship_zip' => request('zip'),
            'ship_phone' => request('phone'),
            'email_opt_in' => request('email_opt_in', 0),
            'donor_id' => request('donor_id'),
            'account_type_id' => request('account_type_id', AccountType::getDefault()->getKey()),
            'referral_source' => request('referral_source'),
            'sms_verified' => false,
        ];

        if ($res = $this->verifySms($data)) {
            return $res;
        }

        if (! app('recaptcha')->verify()) {
            return $this->failure('The CAPTCHA was not completed correctly.');
        }

        $rules = [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:member',
            'password' => 'required|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
            'bill_zip' => 'required|min:5',
            'email_opt_in' => 'boolean',
            'account_type_id' => 'required|exists:account_types,id',
        ];

        // for registrations coming via SMS create the account without a login
        // instead of rejecting the registration for having an already registered email
        if ($data['email'] && $data['sms_verified']) {
            if (Member::where('email', $data['email'])->count()) {
                $data['email'] = null;
                $data['password'] = null;

                unset($rules['email'], $rules['password']);
            }
        }

        $validator = app('validator')->make($data, $rules, [
            'email.unique' => __('frontend/api.validation.email_already_registered'),
            'password.min' => __('frontend/api.validation.password_length_8_characters_min'),
            'password.regex' => __('frontend/api.validation.password_at_least_1_uppercase_lowercase_and_number'),
            'bill_zip.required' => __('frontend/api.validation.missing_postal_code'),
            'bill_zip.min' => __('frontend/api.validation.postal_code_5_characters_min'),
            'account_type_id.required' => __('frontend/api.validation.no_account_type_selected'),
            'account_type_id.exists' => __('frontend/api.validation.account_type_not_found'),
        ]);

        if ($validator->fails()) {
            return $this->failure($validator->errors()->first());
        }

        if (empty($data['bill_organization_name'])) {
            $accountType = AccountType::find($data['account_type_id']);

            if ($accountType->is_organization) {
                return $this->failure(__('frontend/api.missing_organization_name'));
            }
        }

        if ($data['donor_id']) {
            try {
                $donor = app('dpo')
                    ->table('dp')
                    ->select('address', 'city', 'state', 'zip', 'country', 'home_phone')
                    ->where('donor_id', $data['donor_id'])
                    ->get()
                    ->first();

                if (! $donor) {
                    return $this->failure(__('frontend/api.donor_id_not_found', ['donor_id' => $data['donor_id']]));
                }

                // clean postal codes prior to matching
                $data['bill_zip'] = strtoupper(str_replace(' ', '', $data['bill_zip']));
                $data['ship_zip'] = strtoupper(str_replace(' ', '', $data['ship_zip']));
                $donor->zip = strtoupper(str_replace(' ', '', $donor->zip));

                if (! Str::startsWith($donor->zip, $data['bill_zip'])) {
                    return $this->failure(__('frontend/api.could_not_verify_postal_code'));
                }

                $data['bill_address_01'] = $donor->address;
                $data['bill_city'] = $donor->city;
                $data['bill_state'] = $donor->state;
                $data['bill_country'] = $donor->country;
                $data['bill_phone'] = $donor->home_phone;
                $data['ship_address_01'] = $donor->address;
                $data['ship_city'] = $donor->city;
                $data['ship_state'] = $donor->state;
                $data['ship_country'] = $donor->country;
                $data['ship_phone'] = $donor->home_phone;
            } catch (Throwable $e) {
                return $this->failure($e);
            }
        }

        if ($data['password']) {
            $data['password'] = bcrypt($data['password']);
        }

        try {
            $member = Member::register($data, true);
        } catch (Throwable $e) {
            return $this->failure($e);
        }

        if ($member->sms_verified) {
            return $this->success([
                'account' => Drop::factory($member, 'Account'),
            ]);
        }

        $redirectTo = data_get(
            $member,
            'membership.default_url',
            'account/home'
        );

        return $this->success([
            'redirect_to' => session()->pull('url.website_intended', secure_site_url($redirectTo)),
        ]);
    }

    /**
     * @param array $data
     * @return \Illuminate\Http\JsonResponse|null
     */
    public function verifySms(&$data, $hashPassword = false)
    {
        if (request('verify_sms')) {
            try {
                $phone = decrypt(request('verify_sms'));
            } catch (Throwable $e) {
                return $this->failure(__('frontend/api.invalid_signin_attempt'));
            }

            $found = Member::query()
                ->billPhoneE164($phone)
                ->where('sms_verified', true)
                ->count();

            if ($found) {
                return $this->failure(__('frontend/api.device_already_linked'));
            }

            if (empty($data['password'])) {
                $data['password'] = Str::random(64);

                if ($hashPassword) {
                    $data['password'] = bcrypt($data['password']);
                }
            }

            $data['bill_phone'] = $phone;
            $data['sms_verified'] = true;
        }
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(Member $account)
    {
        $data = request()->only([
            'password',
            'password_confirmation',
        ]);

        $validator = app('validator')->make($data, [
            'password' => 'required|confirmed|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
        ], [
            'password.min' => __('frontend/api.validation.password_length_8_characters_min'),
            'password.regex' => __('frontend/api.validation.password_at_least_1_uppercase_lowercase_and_number'),
        ]);

        if ($validator->fails()) {
            return $this->failure($validator->errors()->first());
        }

        $account->password = bcrypt($data['password']);
        $account->force_password_reset = false;
        $account->save();

        return $this->success([
            'redirect_to' => session()->pull('url.website_intended', secure_site_url('account/home')),
        ]);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetPasswordProcess()
    {
        $data = request()->only(['email']);

        $validator = app('validator')->make($data, [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return $this->failure($validator->errors()->first());
        }

        try {
            $member = Member::query()
                ->where('email', $data['email'])
                ->where('is_active', true)
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return $this->failure(__('frontend/api.account_not_found'));
        }

        $password = Str::random(12);

        $member->password = bcrypt($password);
        $member->force_password_reset = 1;
        $member->save();

        if (member_notify_forgot_password($member->id, $password)) {
            $emailTemplate = Email::where('type', 'member_password_reset')->first();

            if (Str::contains($emailTemplate->body_template, '[[password_reset_link]]')) {
                $message = __('frontend/api.emailed_you_a_password_reset_link');
            } else {
                $message = __('frontend/api.emailed_you_a_temporary_password');
            }

            return $this->success([
                'success' => true,
                'message' => $message,
            ]);
        }

        return $this->failure();
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveEmail()
    {
        $data = request()->only([
            'first_name',
            'last_name',
            'email',
        ]);

        $validator = app('validator')->make($data, [
            'first_name' => 'nullable',
            'last_name' => 'nullable',
            'email' => 'required|email|unique:member',
        ], [
            'email.unique' => __('frontend/api.email_already_registered'),
        ]);

        if ($validator->fails()) {
            return $this->failure($validator->errors()->first());
        }

        if (member_sign_up_email_only($data)) {
            return $this->success();
        }

        return $this->failure();
    }

    public function checkEmail()
    {
        $data = request()->only([
            'email',
        ]);

        $validator = app('validator')->make($data, [
            'email' => 'required|email',
        ], [
            'email' => __('frontend/api.enter_valid_email'),
        ]);

        if ($validator->fails()) {
            return $this->failure($validator->errors()->first());
        }

        if (Member::where('email', $data['email'])->count() > 0) {
            return ['exists' => true];
        }

        return ['exists' => false];
    }
}
