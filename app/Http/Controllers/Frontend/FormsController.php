<?php

namespace Ds\Http\Controllers\Frontend;

use Ds\Mail\SubmitToEmail;
use Ds\Models\GroupAccountTimespan;
use Ds\Models\Member;
use Ds\Models\Membership;
use Ds\Services\DonorPerfectService;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;
use Throwable;

class FormsController extends Controller
{
    public function verifyCaptcha()
    {
        return ['verified' => app('recaptcha')->verify()];
    }

    public function submitToEmail()
    {
        try {
            $payload = decrypt(base64_decode(request('payload')));
        } catch (DecryptException $e) {
            abort(422);
        }

        if (! is_array($payload)) {
            abort(422);
        }

        $success = function ($message) use ($payload) {
            $redirectTo = Arr::get($payload, 'success_url');
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'message' => $message,
                    'redirect_to' => $redirectTo,
                ]);
            }

            if ($redirectTo) {
                return redirect()->to($redirectTo)->with('liquid_req.success', $message);
            }

            return redirect()->back()->with('liquid_req.success', $message);
        };

        $failure = function ($message) use ($payload) {
            $redirectTo = Arr::get($payload, 'fail_url');
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'message' => $message,
                    'redirect_to' => $redirectTo,
                ], 422);
            }

            if ($redirectTo) {
                return redirect()->to($redirectTo)->with('liquid_req.error', $message);
            }

            return redirect()->back()->with('liquid_req.error', $message);
        };

        $fields = request()->except([
            'payload',
            'email_subject',
            'g-recaptcha-response',
            'h-captcha-response',
        ]);

        $captcha = Arr::get($payload, 'captcha', 'true');
        $subject = request('email_subject', 'Form Submission');

        if ($captcha === 'true' && ! app('recaptcha')->verify()) {
            return $failure(__('general.captcha.validation_failed'));
        }

        foreach ($fields as &$value) {
            if (is_array($value)) {
                $value = implode(', ', $value);
            }
        }

        $mailable = new SubmitToEmail($subject, $fields);
        $mailable->from('notifications@givecloud.co', 'Givecloud');

        if ($to = Arr::get($payload, 'email_to')) {
            foreach (str_getcsv($to) as $email) {
                $mailable->to(trim($email));
            }
        }

        if ($cc = Arr::get($payload, 'email_cc')) {
            foreach (str_getcsv($cc) as $email) {
                $mailable->cc(trim($email));
            }
        }

        if ($bcc = Arr::get($payload, 'email_bcc')) {
            foreach (str_getcsv($bcc) as $email) {
                $mailable->bcc(trim($email));
            }
        }

        try {
            Mail::send($mailable);
        } catch (Throwable $e) {
            notifyException($e);

            return $failure(__('frontend/forms.submission_failed'));
        }

        return $success(__('frontend/forms.submission_successful'));
    }

    public function signup()
    {
        try {
            $payload = decrypt(base64_decode(request('payload')));
        } catch (DecryptException $e) {
            abort(422);
        }

        if (! is_array($payload)) {
            abort(422);
        }

        $success_url = Arr::get($payload, 'success_url');

        $success = function ($message, $redirectTo) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'message' => $message,
                    'redirect_to' => $redirectTo,
                ]);
            }

            if ($redirectTo) {
                return redirect()->to($redirectTo)->with('liquid_req.success', $message);
            }

            return redirect()->back()->with('liquid_req.success', $message);
        };

        $failure = function ($message) use ($payload) {
            $redirectTo = Arr::get($payload, 'fail_url');
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'message' => $message,
                    'redirect_to' => $redirectTo,
                ], 422);
            }

            if ($redirectTo) {
                return redirect()->to($redirectTo)->with('liquid_req.error', $message);
            }

            return redirect()->back()->with('liquid_req.error', $message);
        };

        $captcha = Arr::get($payload, 'captcha', 'true');
        if ($captcha === 'true' && ! app('recaptcha')->verify()) {
            return $failure(__('general.captcha.validation_failed'));
        }

        $member = Member::where('email', request('email'))->first();

        if (! $member) {
            $member = new Member;
            $member->first_name = request('first_name');
            $member->last_name = request('last_name');
            $member->email = request('email');
            $member->bill_first_name = request('first_name');
            $member->bill_last_name = request('last_name');
            $member->bill_organization_name = request('organization_name');
            $member->bill_email = request('email');
            $member->bill_phone = request('phone');
            $member->bill_address_01 = request('address');
            $member->bill_address_02 = request('address_2');
            $member->bill_city = request('city');
            $member->bill_state = request('state');
            $member->bill_zip = request('zip');
            $member->bill_country = request('country');
            $member->save();
        }

        if ($group_id = Arr::get($payload, 'group')) {
            $group = Membership::find($group_id);

            if ($group) {
                if ($group->default_url) {
                    $success_url = $group->default_url;
                }

                $groupAccount = $member->addUniqueGroup($group, fromLocal('today'), 'Sign-Up Form', array_filter([
                    'client_ip' => request()->ip(),
                    'client_browser' => request()->server('HTTP_USER_AGENT'),
                    'http_referer' => request()->server('HTTP_REFERER'),
                    'tracking_source' => request('utm_source'),
                    'tracking_medium' => request('utm_medium'),
                    'tracking_campaign' => request('utm_campaign'),
                    'tracking_term' => request('utm_term'),
                    'tracking_content' => request('utm_content'),
                    'created_at' => toUtcFormat('today', 'date'),
                ]));

                $groupAccountTimespan =
                    GroupAccountTimespan::query()
                        ->where('account_id', $member->id)
                        ->where('group_id', $groupAccount->group_id)
                        ->where('start_date', $groupAccount->start_date)
                        ->latest('start_date')
                        ->first();

                try {
                    $dp = app(DonorPerfectService::class);

                    $dp->pushAccount($member);
                    $dp->updateDonorMembership($member->donor_id, $groupAccountTimespan);
                } catch (Throwable $e) {
                    // do nothing
                }
            }
        }

        if (request('email_opt_in')) {
            $member->email_opt_in = 1;
            $member->save();
        }

        return $success(__('frontend/forms.sign_up_successful'), $success_url);
    }
}
