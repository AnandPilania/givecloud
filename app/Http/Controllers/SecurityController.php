<?php

namespace Ds\Http\Controllers;

use Illuminate\Support\Facades\DB;

class SecurityController extends Controller
{
    /**
     * Show the security settings page
     */
    public function index()
    {
        user()->canOrRedirect('admin.security');

        pageSetup('Security', 'jpanel');

        return view('settings/security', [
            '__menu' => 'admin.advanced',
            'chartData' => $this->getHistoricalChartData(),
            'paymentsDisabledUntil' => sys_get('datetime:public_payments_disabled_until'),
        ]);
    }

    /**
     * Save the security settings.
     */
    public function save()
    {
        user()->canOrRedirect('admin.security');

        $rules = [
            'two_factor_authentication' => 'required|in:optional,prompt,force',
            'captcha_type' => 'required|in:recaptcha,hcaptcha',
            'ss_auth_attempts' => 'required|integer|between:0,3',
            'public_payments_disabled' => 'required|boolean',
            'require_ip_country_match' => 'required|boolean',
            'checkout_min_value' => 'required|numeric|min:0',
            'arm_rate_threshold' => 'required|numeric|between:0.5,0.95',
            'arm_evaluation_window' => 'required|integer|between:5,60',
            'arm_attempt_threshold' => 'required|integer|min:10',
            'arm_immediate_action' => 'required|in:none,always_require_captcha,stop_accepting_payments',
            'arm_recipients' => 'nullable|array',
            'arm_renotify_recipients' => 'required|integer|min:0',
        ];

        $data = request()->only(array_keys($rules));

        $validator = app('validator')->make($data, $rules, [
            'checkout_min_value.*' => 'Invalid minimum payment amount.',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->first(), 422);
        }

        sys_set($data);

        return response()->json(['success' => true]);
    }

    /**
     * Get historical chart data.
     *
     * @return array
     */
    public function getHistoricalChartData()
    {
        $chartData = [
            'labels' => [],
            'dataset1' => [],
            'dataset2' => [],
            'minutes' => request('minutes', 240),
            'grouping' => null,
            'tickCount' => null,
        ];

        if ($chartData['minutes'] <= 120) { // 2h
            $chartData['grouping'] = 1;
        } elseif ($chartData['minutes'] <= 240) { // 4h
            $chartData['grouping'] = 2;
        } elseif ($chartData['minutes'] <= 480) { // 8h
            $chartData['grouping'] = 3;
        } elseif ($chartData['minutes'] <= 720) { // 12h
            $chartData['grouping'] = 5;
        } elseif ($chartData['minutes'] <= 1440) { // 1d
            $chartData['grouping'] = 10;
        } elseif ($chartData['minutes'] <= 2880) { // 2d
            $chartData['grouping'] = 30;
        } elseif ($chartData['minutes'] <= 10080) { // 1wk
            $chartData['grouping'] = 120;
        } elseif ($chartData['minutes'] <= 40320) { // 1mth
            $chartData['grouping'] = 720;
        } else {
            $chartData['grouping'] = 1440;
        }

        $chartData['tickCount'] = ceil($chartData['minutes'] / $chartData['grouping']);

        $startTime = now()
            ->startOfMinute()
            ->subMinutes($chartData['minutes'])
            ->toLocal();

        $paymentData = DB::table('payments')
            ->select([
                DB::raw("FLOOR(UNIX_TIMESTAMP(created_at)/({$chartData['grouping']} * 60)) as interval_key"),
                DB::raw("SUM(IF(status IN ('succeeded','pending'), 1, 0)) as successful"),
                DB::raw("SUM(IF(status IN ('failed'             ), 1, 0)) as failed"),
            ])->where('created_at', '>', $startTime->toUtc())
            ->groupBy('interval_key')
            ->get()
            ->keyBy('interval_key');

        while (count($chartData['labels']) < $chartData['tickCount']) {
            $key = floor($startTime->getTimestamp() / ($chartData['grouping'] * 60));
            $chartData['labels'][] = toLocal($startTime)->format('H:i');
            $chartData['dataset1'][] = (int) data_get($paymentData, "$key.failed");
            $chartData['dataset2'][] = (int) data_get($paymentData, "$key.successful");
            $startTime->addMinutes($chartData['grouping']);
        }

        return $chartData;
    }
}
