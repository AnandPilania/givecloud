<?php

namespace Ds\Http\Controllers;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Ds\Domain\Commerce\Currency;
use Ds\Domain\Commerce\Models\PaymentProvider;
use Ds\Domain\Shared\DataTable;
use Ds\Domain\Shared\DateTime;
use Ds\Enums\RecurringPaymentProfileStatus;
use Ds\Events\RecurringPaymentWasCompleted;
use Ds\Models\AccountType;
use Ds\Models\PaymentMethod;
use Ds\Models\RecurringPaymentProfile;
use Ds\Services\DonorCoversCostsService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use LiveControl\EloquentDataTable\ExpressionWithName;
use Throwable;

class RecurringPaymentsController extends Controller
{
    public function index()
    {
        if (request('id')) {
            return redirect()->to('/jpanel/recurring_payments/' . RecurringPaymentProfile::findOrFail(request('id'))->profile_id);
        }

        user()->canOrRedirect('recurringpaymentprofile');

        $filters = (object) [];
        $filters->search = request()->query('search');
        $filters->status = request()->query('status', RecurringPaymentProfileStatus::ACTIVE);
        $filters->startdate_str = request()->query('startdate_str');
        $filters->startdate_end = request()->query('startdate_end');
        $filters->enddate_str = request()->query('enddate_str');
        $filters->enddate_end = request()->query('enddate_end');
        $filters->nextbilldate_str = request()->query('nextbilldate_str');
        $filters->nextbilldate_end = request()->query('nextbilldate_end');
        $filters->payment_provider = request()->query('payment_provider');
        $filters->payment_method_type = request()->query('payment_method_type');
        $filters->payment_method_status = request()->query('payment_method_status');
        $filters->currency_code = request()->query('currency_code');
        $filters->frequency = request()->query('frequency');
        $filters->profile_type = request()->query('profile_type');
        $filters->cancel_reason = request()->query('cancel_reason');
        $filters->account_type = request()->query('account_type');

        $total_stats = (object) [
            'active_accounts' => 0,
            'active_amount' => 0,
            'suspended_amount' => 0,
            'cancelled_amount' => 0,
        ];

        DB::table('recurring_payment_profiles')
            ->select([
                DB::raw('MAX(currency_code) as currency_code'),
                DB::raw("SUM(IF(status='" . RecurringPaymentProfileStatus::ACTIVE . "',1,0)) as active_count"),
                DB::raw("SUM(IF(status='" . RecurringPaymentProfileStatus::ACTIVE . "',amt,0)) as active_amount"),
                DB::raw("SUM(IF(status='" . RecurringPaymentProfileStatus::SUSPENDED . "',amt,0)) as suspended_amount"),
                DB::raw("SUM(IF(status='" . RecurringPaymentProfileStatus::CANCELLED . "',amt,0)) as cancelled_amount"),
            ])->groupBy('currency_code')
            ->get()
            ->each(function ($data) use (&$total_stats) {
                $convertCurrency = function ($key) use ($data) {
                    return (float) money($data->{$key}, $data->currency_code)
                        ->toCurrency(sys_get('dpo_currency'))
                        ->getAmount();
                };
                $total_stats->active_accounts += $data->active_count;
                $total_stats->active_amount += $convertCurrency('active_amount');
                $total_stats->suspended_amount += $convertCurrency('suspended_amount');
                $total_stats->cancelled_amount += $convertCurrency('cancelled_amount');
            });

        $status_breakdown_stats = [
            [
                'label' => RecurringPaymentProfileStatus::ACTIVE,
                'value' => $total_stats->active_accounts,
            ],
            [
                'label' => RecurringPaymentProfileStatus::SUSPENDED,
                'value' => RecurringPaymentProfile::where('status', RecurringPaymentProfileStatus::SUSPENDED)->count(),
            ],
            [
                'label' => RecurringPaymentProfileStatus::CANCELLED,
                'value' => RecurringPaymentProfile::where('status', RecurringPaymentProfileStatus::CANCELLED)->count(),
            ],
        ];

        // RPPs start processing at 5AM EST
        $rppProcessingDate = DateTime::parseDateTime('today', 'America/New_York')->setTime(5, 0);
        $rppProcessingDate = now()->lt($rppProcessingDate) ? $rppProcessingDate->asDate() : $rppProcessingDate->asDate()->addDay();

        // grab the soonest next bill date when there are not outstanding RPPs to process
        if (RecurringPaymentProfile::chargeable($rppProcessingDate)->doesntExist()) {
            $rppProcessingDate = RecurringPaymentProfile::active()->where('next_billing_date', '>=', $rppProcessingDate->toDateFormat())->min('next_billing_date');
            $rppProcessingDate = optional(fromUtc($rppProcessingDate))->asDate();
        }

        if ($rppProcessingDate) {
            $accounts_to_charge = RecurringPaymentProfile::chargeable($rppProcessingDate)->count();
            $amount_to_charge = RecurringPaymentProfile::chargeable($rppProcessingDate)
                ->groupBy('currency_code')
                ->get([DB::raw('SUM(amt) as amount'), 'currency_code'])->reduce(function ($carry, $profile_group) {
                    $amount = money($profile_group->amount, $profile_group->currency_code)->toCurrency(sys_get('dpo_currency'))->getAmount();

                    return $carry + $amount;
                });
        }

        return view('recurring_payments.index', [
            'pageTitle' => 'Recurring Payments',
            '__menu' => 'recurring.payments',
            'filters' => $filters,
            'status_breakdown_stats' => $status_breakdown_stats,
            'total_stats' => $total_stats,
            'next_bill_date' => $rppProcessingDate,
            'accounts_to_charge' => $accounts_to_charge ?? null,
            'amount_to_charge' => $amount_to_charge ?? null,
            'currencies' => Currency::getLocalCurrencies(),
            'providers' => PaymentProvider::enabled()->orderBy('display_name')->get(),
            'account_types' => AccountType::all(),
        ]);
    }

    public function index_ajax()
    {
        user()->canOrRedirect('recurringpaymentprofile');

        $rpp = $this->_baseQueryWithFilters();

        // generate data table
        $dataTable = new DataTable($rpp, [
            'profile_id',
            new ExpressionWithName('profile_id', 'col2'),
            'profile_reference',
            'member.display_name',
            'description',
            'profile_start_date',
            'next_billing_date',
            'amt',
            'billing_period',
            'aggregate_amount',
            'status',
            'is_manual',
            'member_id',
            'payment_method_id',
            new ExpressionWithName('recurring_payment_profiles.currency_code', 'currency_code'),
            'is_locked',
            'tax_amt',
            'shipping_amt',
            'dcc_amount',
            'paypal_subscription_id',
            'stripe_subscription_id',
            'productorderitem_id',
        ]);

        $dataTable->setFormatRowFunction(function ($rpp) {
            $badge = '';
            if ($rpp->is_manual && $rpp->is_locked) {
                if ($rpp->paypal_subscription_id || Str::startsWith($rpp->profile_reference, 'PP')) {
                    $badge = '<div class="pull-right label label-xs label-default">Legacy PayPal</div>';
                } elseif ($rpp->stripe_subscription_id || Str::startsWith($rpp->profile_reference, 'ST')) {
                    $badge = '<div class="pull-right label label-xs label-default">Legacy Stripe</div>';
                } else {
                    $badge = '<div class="pull-right label label-xs label-default">Legacy</div>';
                }
            } elseif ($rpp->is_manual) {
                $badge = '<div class="pull-right label label-xs label-default">Manual</div>';
            }

            return [
                dangerouslyUseHTML('<a href="/jpanel/recurring_payments/' . e($rpp->profile_id) . '"><i class="fa fa-search"></i></a>'),
                e($rpp->profile_id),
                dangerouslyUseHTML(sprintf('<a href="%s">%s</a>', route('backend.orders.edit_without_id', ['c' => e($rpp->profile_reference)]), e($rpp->profile_reference))),
                dangerouslyUseHTML('<a href="' . route('backend.member.edit', $rpp->member_id) . '"><i class="fa ' . e($rpp->member->fa_icon) . '"></i> ' . e($rpp->member->display_name) . '</a>'),
                e(isset($rpp->order_item->gl_code) ? "{$rpp->description} (GL: {$rpp->order_item->gl_code})" : $rpp->description),
                e($rpp->profile_start_date . ''),
                e($rpp->next_billing_date . ''),
                dangerouslyUseHTML('<div class="stat-val">' . e(number_format($rpp->total_amt, 2)) . '&nbsp;<span class="text-muted">' . e($rpp->currency_code) . '</span></div>'),
                dangerouslyUseHTML('<i title="' . e($rpp->paymentMethod->name) . '" style="font-size:18px;" class="fa fa-fw ' . e($rpp->paymentMethod->fa_icon) . '"></i>'),
                dangerouslyUseHTML($badge . e($rpp->billing_period)),
                dangerouslyUseHTML('<div class="stat-val">' . e(number_format($rpp->aggregate_amount, 2)) . '&nbsp;<span class="text-muted">' . e($rpp->currency_code) . '</span></div>'),
                e($rpp->status),
            ];
        });

        // return datatable JSON
        return response($dataTable->make());
    }

    public function index_csv()
    {
        // takes a while to export 10,000+ RPPS (our rescue)
        set_time_limit(120);

        user()->canOrRedirect('recurringpaymentprofile');

        $recurringPaymentProfile = $this->_baseQueryWithFilters()
            ->with(['paymentMethod.paymentProvider'])
            ->withFirstPayment()
            ->withLastPaymentAttempt()
            ->withCount(['payments as successful_payments_count' => function ($query) {
                $query->succeededOrPending();
            }, 'payments as unsuccessful_payments_count' => function ($query) {
                $query->failed();
            }]);

        header('Content-type: text/csv');
        header('Content-type: text/plain');
        header('Cache-Control: no-store, no-cache');
        header('Content-Disposition: attachment; filename="recurring_payment_profiles.csv"');
        $outstream = fopen('php://output', 'w');
        $headers = [
            'Profile',
            'Contribution',
            'Subscriber Name',
            'Subscriber Email',
            'Description',
            'GL',
            'Recurring Amount',
            'Currency',
            'Start date',
            'Next billing date',
            'Last billed amount',
            'Lifetime amount',
            'Status',
            'Cancel Reason',
            'Cancelled Date',
            'End Date',
            'Billing Period',
            'Payment Method',
            'Payment Type',
            'Account Last Four',
            'Payment Gateway',
            'Vault ID',
            'Number of Successful Payments',
            'Number of Failed Payments',
            'First Payment Date',
            'First Payment Amount',
            'Last Payment Date',
            'Last Payment Amount',
            'Last Attempt Date',
            'Last Attempt Amount',
            'Last Attempt Status',
        ];

        // include dpo field
        if (dpo_is_enabled()) {
            $headers[] = 'DP Donor ID';
        }

        fputcsv($outstream, $headers, ',', '"');
        $recurringPaymentProfile->chunk(500, function ($profiles) use ($outstream) {
            foreach ($profiles as $profile) {
                fputcsv($outstream, [
                    $profile->profile_id,
                    $profile->profile_reference,
                    $profile->subscriber_name,
                    $profile->member->email ?? $profile->member->bill_email ?? '',
                    $profile->description,
                    $profile->order_item->gl_code ?? '',
                    number_format($profile->total_amt, 2),
                    $profile->currency_code,
                    toLocalFormat($profile->profile_start_date, 'csv'),
                    toLocalFormat($profile->next_billing_date, 'csv'),
                    number_format($profile->last_payment_amt, 2),
                    number_format($profile->aggregate_amount, 2),
                    $profile->status,
                    $profile->cancel_reason,
                    $profile->status === RecurringPaymentProfileStatus::CANCELLED ? toLocalFormat($profile->final_payment_due_date, 'csv') : null,
                    toLocalFormat($profile->final_billing_date, 'csv'),
                    $profile->billing_period,
                    $profile->paymentMethod->display_name,
                    $profile->paymentMethod->account_type,
                    $profile->paymentMethod->account_last_four,
                    $profile->paymentMethod->paymentProvider->display_name ?? '',
                    $profile->paymentMethod->token,

                    $profile->successful_payments_count,
                    $profile->unsuccessful_payments_count,

                    toLocalFormat($profile->firstPayment->created_at ?? null, 'csv'),
                    number_format($profile->firstPayment->amount ?? 0, 2),

                    toLocalFormat($profile->last_payment_date ?? null, 'csv'),
                    number_format($profile->last_payment_amt ?? 0, 2),

                    toLocalFormat($profile->lastPaymentAttempt->created_at ?? null, 'csv'),
                    number_format($profile->lastPaymentAttempt->amount ?? 0, 2),
                    $profile->lastPaymentAttempt->status ?? null,

                    (dpo_is_enabled()) ? $profile->member->donor_id : '',
                ], ',', '"');
            }
        });

        fclose($outstream);
        exit;
    }

    /**
     * Build a base query based on request filter params.
     * Allows us to reuse this for datatables, csv, etc...
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function _baseQueryWithFilters()
    {
        $rpp = RecurringPaymentProfile::with('member.accountType', 'paymentMethod', 'order_item')
            ->leftJoin('member', 'member.id', '=', 'recurring_payment_profiles.member_id');

        $filters = (object) [];
        $filters->search = request('search');
        if ($filters->search) {
            $rpp->where(function ($query) use ($filters) {
                $query->where('subscriber_name', 'like', "%$filters->search%");
                $query->orWhere('description', 'like', "%$filters->search%");
                $query->orWhere('profile_reference', 'like', "%$filters->search%");
                $query->orWhere('profile_id', 'like', "%$filters->search%");
                $query->orWhereExists(function ($q) use ($filters) {
                    $q->select(DB::raw(1))
                        ->from('member')
                        ->whereRaw('member.id = recurring_payment_profiles.member_id')
                        ->where('member.display_name', 'like', "%$filters->search%");
                });
            });
        }

        $filters->status = request('status');
        if ($filters->status) {
            $rpp->where('status', $filters->status);
        }

        $filters->currency_code = request('currency_code');
        if ($filters->currency_code) {
            $rpp->where('recurring_payment_profiles.currency_code', $filters->currency_code);
        }

        $filters->frequency = request('frequency');
        if ($filters->frequency) {
            $rpp->where('billing_period', $filters->frequency);
        }

        $filters->profile_type = request('profile_type');
        if ($filters->profile_type === 'auto') {
            $rpp->where('is_manual', false);
            $rpp->where('is_locked', false);
        } elseif ($filters->profile_type === 'manual') {
            $rpp->where('is_manual', true);
            $rpp->where('is_locked', false);
        } elseif ($filters->profile_type === 'legacy') {
            $rpp->where('is_manual', true);
            $rpp->where('is_locked', true);
        }

        $filters->cancel_reason = request('cancel_reason');
        if ($filters->cancel_reason) {
            $rpp->where('cancel_reason', $filters->cancel_reason);
        }

        $filters->account_type = request('account_type');
        if ($filters->account_type) {
            $rpp->whereHas('member', function ($query) use ($filters) {
                $query->where('account_type_id', $filters->account_type);
            });
        }

        // make sure that date filtering is inclusive
        $filters->startdate_str = request('startdate_str');
        $filters->startdate_end = request('startdate_end');
        if ($filters->startdate_str && $filters->startdate_end) {
            $rpp->whereBetween('profile_start_date', [
                fromUtc($filters->startdate_str)->startOfDay(),
                fromUtc($filters->startdate_end)->endOfDay(),
            ]);
        } elseif ($filters->startdate_str) {
            $rpp->where('profile_start_date', '>=', fromUtc($filters->startdate_str)->startOfDay());
        } elseif ($filters->startdate_end) {
            $rpp->where('profile_start_date', '<=', fromUtc($filters->startdate_end)->endOfDay());
        }

        // make sure that date filtering is inclusive
        $filters->nextbilldate_str = request('nextbilldate_str');
        $filters->nextbilldate_end = request('nextbilldate_end');
        if ($filters->nextbilldate_str && $filters->nextbilldate_end) {
            $rpp->whereBetween('next_billing_date', [
                fromLocal($filters->nextbilldate_str)->toDateFormat(),
                fromLocal($filters->nextbilldate_end)->toDateFormat(),
            ]);
        } elseif ($filters->nextbilldate_str) {
            $rpp->where('next_billing_date', '>=', fromLocal($filters->nextbilldate_str)->toDateFormat());
        } elseif ($filters->nextbilldate_end) {
            $rpp->where('next_billing_date', '<=', fromLocal($filters->nextbilldate_end)->toDateFormat());
        }

        // make sure that date filtering is inclusive
        $filters->enddate_str = request('enddate_str');
        $filters->enddate_end = request('enddate_end');
        if ($filters->enddate_str && $filters->enddate_end) {
            $rpp->whereBetween('final_payment_due_date', [
                fromLocal($filters->enddate_str)->startOfDay()->toUtc(),
                fromLocal($filters->enddate_end)->endOfDay()->toUtc(),
            ]);
        } elseif ($filters->enddate_str) {
            $rpp->where('final_payment_due_date', '>=', fromLocal($filters->enddate_str)->startOfDay()->toUtc());
        } elseif ($filters->enddate_end) {
            $rpp->where('final_payment_due_date', '<=', fromLocal($filters->enddate_end)->endOfDay()->toUtc());
        }

        $filters->payment_provider = request('payment_provider');
        if ($filters->payment_provider) {
            $rpp->where(function ($query) use ($filters) {
                $query->whereHas('paymentMethod', function ($query) use ($filters) {
                    $query->whereNull('deleted_at')
                        ->where('payment_provider_id', $filters->payment_provider);
                })->orWhere(function ($query) use ($filters) {
                    $query->where(function (Builder $query) {
                        $query->whereDoesntHave('paymentMethod')
                            ->orWhereHas(
                                'paymentMethod',
                                fn ($query) => $query->whereNotNull('deleted_at')
                            );
                    })->whereHas(
                        'member.defaultPaymentMethod',
                        fn ($query) => $query->where('payment_provider_id', $filters->payment_provider)
                    );
                });
            });
        }

        $filters->payment_method_type = request('payment_method_type');
        if ($filters->payment_method_type) {
            $rpp->whereHas('paymentMethod', function ($query) use ($filters) {
                if ($filters->payment_method_type === 'Amex') {
                    $query->where('account_type', 'American Express');
                } elseif ($filters->payment_method_type === 'ACH') {
                    $query->whereIn('account_type', [
                        'Business Checking',
                        'Business Savings',
                        'Individual Checking',
                        'Individual Savings',
                        'Personal Checking',
                        'Personal Savings',
                    ]);
                } elseif ($filters->payment_method_type === 'Other') {
                    $query->whereNotIn('account_type', [
                        'American Express',
                        'Business Checking',
                        'Business Savings',
                        'Diners Club',
                        'Discover',
                        'Individual Checking',
                        'Individual Savings',
                        'JCB',
                        'MasterCard',
                        'PayPal',
                        'Personal Checking',
                        'Personal Savings',
                        'Visa',
                    ]);
                } else {
                    $query->where('account_type', $filters->payment_method_type);
                }
            });
        }

        $filters->payment_method_status = request('payment_method_status');
        if ($filters->payment_method_status == 'active') {
            $rpp->whereHas('paymentMethod', function ($query) {
                $query->where('cc_expiry', '>=', now()->startOfDay())
                    ->whereNull('deleted_at');
            });
        } elseif ($filters->payment_method_status == 'expiring') {
            $rpp->whereHas('paymentMethod', function ($query) {
                $query->where('cc_expiry', '<', now()->addDays(30))
                    ->whereNull('deleted_at');
            });
        } elseif ($filters->payment_method_status == 'expired') {
            $rpp->whereHas('paymentMethod', function ($query) {
                $query->where('cc_expiry', '<', now()->startOfMonth())
                    ->whereNull('deleted_at');
            });
        } elseif ($filters->payment_method_status == 'deleted') {
            $rpp->whereHas('paymentMethod', function ($query) {
                $query->whereNotNull('deleted_at');
            });
        } elseif ($filters->payment_method_status == 'expired_deleted') {
            $rpp->whereHas('paymentMethod', function ($query) {
                $query->whereNotNull('deleted_at')
                    ->orWhere('cc_expiry', '<', now()->startOfMonth());
            });
        }

        return $rpp;
    }

    public function show($profileId)
    {
        user()->canOrRedirect('recurringpaymentprofile');

        $profile = RecurringPaymentProfile::with(['member', 'transactions', 'paymentMethod'])->where('profile_id', $profileId)->first();

        if (! $profile) {
            return redirect()->back();
        }

        $paymentMethods = $profile->member->paymentMethods()
            ->active()
            ->get()
            ->reject(fn ($paymentMethod) => $paymentMethod->id === $profile->payment_method_id)
            ->sortBy([
                ['is_expired', 'asc'],
                ['created_at', 'desc'],
            ])->when($profile->paymentMethod, fn ($paymentMethods) => $paymentMethods->prepend($profile->paymentMethod));

        return view('recurring_payments.show', [
            '__menu' => 'recurring.payments',
            'pageTitle' => $profile->member->display_name . ' - ' . $profile->payment_string . ' - ' . $profile->profile_id,
            'recurring_payment_profile' => $profile,
            'payment_methods' => $paymentMethods,
            'initial_order' => $profile->order,
        ]);
    }

    public function edit($profileId)
    {
        user()->canOrRedirect('recurringpaymentprofile.edit');

        $recurringPaymentProfile = RecurringPaymentProfile::findOrFail($profileId);

        return view('recurring_payments.edit', [
            '__menu' => 'recurring.payments',
            'payment_days' => explode(',', sys_get('payment_day_options')),
            'payment_days_of_week' => explode(',', sys_get('payment_day_of_week_options')),
            'recurring_payment_profile' => $recurringPaymentProfile,
            'payment_methods' => $recurringPaymentProfile->member->paymentMethods()->active()->get(),
            'dcc_amounts' => app(DonorCoversCostsService::class)->getCosts($recurringPaymentProfile->amt),
            'has_original_dcc' => $recurringPaymentProfile->dcc_type && ! in_array($recurringPaymentProfile->dcc_amount, app(DonorCoversCostsService::class)->getCosts($recurringPaymentProfile->amt)),
        ]);
    }

    public function saveEdits($profileId)
    {
        user()->canOrRedirect('recurringpaymentprofile.edit');

        $recurringPaymentProfile = RecurringPaymentProfile::find($profileId);

        if (! $recurringPaymentProfile) {
            abort(404);
        }

        // only allow payment methods that belong to the account holder
        if (request()->filled('payment_method_id')) {
            $recurringPaymentProfile->payment_method_id = request('payment_method_id');

            $notAvailable = $recurringPaymentProfile->member->paymentMethods()
                ->where('id', $recurringPaymentProfile->payment_method_id)
                ->active()
                ->doesntExist();

            if ($notAvailable) {
                $this->flash->error('The payment method selected is not available.');

                return redirect()->to("jpanel/recurring_payments/$profileId/edit");
            }
        }

        // only accept amounts over $0.00
        $recurringPaymentProfile->amt = (float) request('amt');
        if ($recurringPaymentProfile->amt <= 0) {
            $this->flash->error('The amount due must be greater than $0.00.');

            return redirect()->to("jpanel/recurring_payments/$profileId/edit");
        }

        // set the billing period
        $recurringPaymentProfile->billing_period = request('billing_period');
        if (! in_array($recurringPaymentProfile->billing_period, ['Day', 'Week', 'SemiMonth', 'Month', 'Quarter', 'SemiYear', 'Year'])) {
            $this->flash->error('An error occurred while saving recurring payment details.');

            return redirect()->to("jpanel/recurring_payments/$profileId/edit");
        }

        // set new next billing date
        // if (request('change_next_billing_date')) {
        //    $recurringDay = (int)request('recurring_day');
        //    $recurringDayOfWeek = (int)request('recurring_day_of_week');
        //    $recurringPaymentProfile->next_billing_date = $recurringPaymentProfile->getFirstPossibleStartDate('fixed', $recurringDay, $recurringDayOfWeek, null);
        // }

        if (request('next_billing_date_override')) {
            $next_date = Carbon::parse(request('next_billing_date_override'));
            if ($next_date->isFuture()) {
                $recurringPaymentProfile->billing_cycle_anchor = $next_date->copy();
                $recurringPaymentProfile->next_billing_date = $next_date;
            }
        }

        if (! $recurringPaymentProfile->is_locked && request('num_cycles_remaining') > 0) {
            $recurringPaymentProfile->final_payment_due_date = null;
            $recurringPaymentProfile->num_cycles_remaining = request('num_cycles_remaining');
        }

        // final payment date
        if (! $recurringPaymentProfile->is_locked && request('final_payment_due_date')) {
            $recurringPaymentProfile->final_payment_due_date = \Carbon\Carbon::createFromFormat('M d, Y', request('final_payment_due_date'));

            if ($recurringPaymentProfile->final_payment_due_date->isPast()) {
                $recurringPaymentProfile->num_cycles_remaining = 0;
            } else {
                $remainingPeriods = CarbonPeriod::create($recurringPaymentProfile->next_billing_date, $recurringPaymentProfile->iso8601_billing_period, $recurringPaymentProfile->final_payment_due_date);

                if ($recurringPaymentProfile->final_payment_due_date->eq($remainingPeriods->getEndDate())) {
                    $remainingPeriods->toggleOptions(CarbonPeriod::EXCLUDE_END_DATE, true);
                }

                $recurringPaymentProfile->num_cycles_remaining = max(0, count($remainingPeriods));
            }
        }

        if (! $recurringPaymentProfile->is_locked && ! request('final_payment_due_date') && $recurringPaymentProfile->final_payment_due_date) {
            $recurringPaymentProfile->final_payment_due_date = null;
            $recurringPaymentProfile->num_cycles_remaining = null;
        }

        if (request()->has('dcc_enabled_by_customer')) {
            $recurringPaymentProfile->dcc_enabled_by_customer = (bool) request('dcc_enabled_by_customer');
        }

        if ($recurringPaymentProfile->isDirty(['amt', 'dcc_enabled_by_customer'])
            && (! sys_get('dcc_ai_is_enabled') || request('dcc_type') === 'original')) {
            if ($recurringPaymentProfile->dcc_enabled_by_customer) {
                $recurringPaymentProfile->dcc_per_order_amount = $recurringPaymentProfile->order->dcc_per_order_amount;
                $recurringPaymentProfile->dcc_rate = $recurringPaymentProfile->order->dcc_rate;
                $recurringPaymentProfile->dcc_amount = round($recurringPaymentProfile->dcc_per_order_amount + ($recurringPaymentProfile->amt * $recurringPaymentProfile->dcc_rate / 100), 2);
            } else {
                $recurringPaymentProfile->dcc_per_order_amount = 0;
                $recurringPaymentProfile->dcc_rate = 0;
                $recurringPaymentProfile->dcc_amount = 0;
            }
        }

        if (request('dcc_type') !== 'original') {
            $recurringPaymentProfile->dcc_type = request('dcc_type');
        }

        if (sys_get('dcc_ai_is_enabled')
            && request('dcc_type') !== 'original'
            && $recurringPaymentProfile->isDirty(['amt', 'dcc_type'])) {
            $recurringPaymentProfile->dcc_enabled_by_customer = (bool) request('dcc_type');
            $recurringPaymentProfile->dcc_rate = 0;
            $recurringPaymentProfile->dcc_per_order_amount = 0;
            $recurringPaymentProfile->dcc_amount = app(DonorCoversCostsService::class)->getCost($recurringPaymentProfile->amt, $recurringPaymentProfile->dcc_type);
        }

        $recurringPaymentProfile->is_manual = ($recurringPaymentProfile->is_locked || request('is_manual') == 1);

        $recurringPaymentProfile->save();

        // make sure there is a default payment method set
        if (! $recurringPaymentProfile->is_manual && ! $recurringPaymentProfile->payment_method_id) {
            if ($default_method = $recurringPaymentProfile->member->defaultPaymentMethod) {
                $recurringPaymentProfile->payment_method_id = $default_method->id;
                $recurringPaymentProfile->save();
            } else {
                $recurringPaymentProfile->is_manual = 1;
                $recurringPaymentProfile->save();
                $this->flash->error('The recurring payment cannot be automatic because there is no default Payment Method on the account.');
            }
        }

        $this->flash->success('Successfully saved recurring payment.');

        return redirect()->to('jpanel/recurring_payments/' . $profileId);
    }

    public function processCancellation($profileId)
    {
        user()->canOrRedirect('recurringpaymentprofile.edit');

        $recurringPaymentProfile = RecurringPaymentProfile::find($profileId);

        if (! $recurringPaymentProfile) {
            abort(404);
        }

        $recurringPaymentProfile->cancelProfile(request('cancel_reason'));

        $this->flash->success('Successfully cancelled recurring payment.');

        return redirect()->to('jpanel/recurring_payments/' . $profileId);
    }

    public function updateCancelReason($profileId)
    {
        user()->canOrRedirect('recurringpaymentprofile.edit');

        $recurringPaymentProfile = RecurringPaymentProfile::find($profileId);

        if (! $recurringPaymentProfile) {
            abort(404);
        }

        $recurringPaymentProfile->updateCancelReason(request('cancel_reason'));

        $this->flash->success('Successfully updated recurring payment cancel reason.');

        return redirect()->to('jpanel/recurring_payments/' . $profileId);
    }

    public function suspend($profileId)
    {
        user()->canOrRedirect('recurringpaymentprofile.edit');

        $recurringPaymentProfile = RecurringPaymentProfile::find($profileId);

        if (! $recurringPaymentProfile || $recurringPaymentProfile->status !== RecurringPaymentProfileStatus::ACTIVE) {
            abort(404);
        }

        // prevent modifying locked profiles
        if ($recurringPaymentProfile->is_locked) {
            $this->flash->error('Locked profiles can not be suspended.');

            return redirect()->to("jpanel/recurring_payments/$profileId");
        }

        $recurringPaymentProfile->status = RecurringPaymentProfileStatus::SUSPENDED;
        $recurringPaymentProfile->save();

        $this->flash->success('Successfully suspended recurring payment.');

        return redirect()->to('jpanel/recurring_payments/' . $profileId);
    }

    public function overridePledgeId($profileId)
    {
        user()->canOrRedirect('recurringpaymentprofile.edit');

        $recurringPaymentProfile = RecurringPaymentProfile::find($profileId);

        if (! $recurringPaymentProfile || $recurringPaymentProfile->status !== RecurringPaymentProfileStatus::ACTIVE) {
            abort(404);
        }

        $pledge_id = request('dp-pledge-id');

        if (empty($pledge_id)) {
            $recurringPaymentProfile->dp_pledge_id_override = null;
            $recurringPaymentProfile->save();
            $this->flash->success('Successfully removed DonorPerfect Pledge ID override.');
        } else {
            $gift = app('Ds\Services\DonorPerfectService')->gift($pledge_id);
            if ($gift && $gift->record_type == 'P') {
                $recurringPaymentProfile->dp_pledge_id_override = $pledge_id;
                $recurringPaymentProfile->save();
                $this->flash->success('Successfully saved DonorPerfect Pledge ID override.');
            } else {
                $this->flash->error('Invalid Pledge ID.');
            }
        }

        return redirect()->to('jpanel/recurring_payments/' . $profileId);
    }

    public function enable($profileId)
    {
        user()->canOrRedirect('recurringpaymentprofile.edit');

        $recurringPaymentProfile = RecurringPaymentProfile::find($profileId);

        if (! $recurringPaymentProfile || $recurringPaymentProfile->status !== RecurringPaymentProfileStatus::SUSPENDED) {
            $this->flash->error('There was a problem activating this profile.');
        } else {
            if ($recurringPaymentProfile->is_locked) {
                $this->flash->error('Locked profiles can not be activated.');

                return redirect()->to("jpanel/recurring_payments/$profileId");
            }

            $recurringPaymentProfile->activateProfile();
            $this->flash->success('Successfully enabled recurring payment.');
        }

        return redirect()->to('jpanel/recurring_payments/' . $profileId);
    }

    /**
     * Manually charge a recurring payment profile.
     * Originally intended for testing - but I added
     * a user permission so our clients can use it too.
     *
     * @param string $profileId
     */
    public function charge($profileId)
    {
        // check permissions
        user()->canOrRedirect('recurringpaymentprofile.charge');

        // find the profile
        $recurringPaymentProfile = RecurringPaymentProfile::findOrFail($profileId);

        // prevent charging locked profiles
        if ($recurringPaymentProfile->is_locked) {
            $this->flash->error('Locked profiles can not be charged.');

            return redirect()->to("jpanel/recurring_payments/$profileId");
        }

        // safely try charging the profile
        try {
            // validate payment method
            if (is_numeric(request()->input('payment_method'))) {
                $method = PaymentMethod::findOrFail(request()->input('payment_method'));
            } else {
                $method = request()->input('payment_method');
            }

            $txn = $recurringPaymentProfile->manualCharge($method, [
                'reason_code' => request()->input('note'),
                'transaction_id' => request()->input('reference'),
                'update_next_bill' => (request()->input('change_next_bill_date') == 1),
            ]);

            // if the payment was a success
            if ($txn->is_payment_accepted) {
                $this->flash->success('Successfully process transaction.');

                event(new RecurringPaymentWasCompleted($recurringPaymentProfile, $txn));
            }

            // if the payment was declined
            else {
                $this->flash->error('The transaction was declined. Check the transaction for <a href="#" class="ds-txn" data-txn-id="' . $txn->id . '">more details</a>.');
            }

            // if there's an error trying to charge the card,
        // catch it and return a friendly error
        } catch (Throwable $e) {
            $this->flash->error($e->getMessage());
        }

        // redirect back to the profile we're charging
        return redirect()->to('jpanel/recurring_payments/' . $profileId);
    }
}
