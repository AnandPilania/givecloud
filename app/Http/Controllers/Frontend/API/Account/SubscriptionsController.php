<?php

namespace Ds\Http\Controllers\Frontend\API\Account;

use Ds\Domain\Theming\Liquid\Drop;
use Ds\Enums\RecurringPaymentProfileStatus;
use Ds\Http\Controllers\Frontend\API\Controller;
use Ds\Models\Member as Account;
use Ds\Services\DonorCoversCostsService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\Rule;

class SubscriptionsController extends Controller
{
    /**
     * Register controller middleware.
     */
    protected function registerMiddleware()
    {
        $this->middleware('auth.member');
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSubscriptions(Account $account)
    {
        $subscriptions = $account->recurringPaymentProfiles()
            ->orderByRaw(sprintf(
                "FIELD(status,'%s','%s','%s','%s') ASC, profile_start_date DESC",
                RecurringPaymentProfileStatus::ACTIVE,
                RecurringPaymentProfileStatus::SUSPENDED,
                RecurringPaymentProfileStatus::EXPIRED,
                RecurringPaymentProfileStatus::CANCELLED,
            ))
            ->get();

        return $this->success(Drop::collectionFactory($subscriptions, 'Subscription'));
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSubscription(Account $account, $subscriptionId)
    {
        try {
            $subscription = $account->recurringPaymentProfiles()->hashid($subscriptionId)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return $this->failure(__('frontend/api.subscription_not_found'), 404);
        }

        return $this->success(Drop::factory($subscription, 'Subscription'));
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateSubscription(Account $account, $subscriptionId)
    {
        try {
            $subscription = $account->recurringPaymentProfiles()->hashid($subscriptionId)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return $this->failure(__('frontend/api.subscription_not_found'), 404);
        }

        if ($subscription->status === RecurringPaymentProfileStatus::EXPIRED) {
            return $this->failure(__('frontend/api.cannot_update_expired_subscription'));
        }

        if ($subscription->status === RecurringPaymentProfileStatus::CANCELLED) {
            return $this->failure(__('frontend/api.cannot_update_cancelled_subscription'));
        }

        if ($subscription->is_locked) {
            return $this->failure(__('frontend/api.cannot_update_locked_subscription'));
        }

        // if ($subscription->transaction_type === 'Donation') {
        $validator = app('validator')->make(request()->all(), [
            'amount' => 'numeric|min:0',
            'billing_period' => 'in:Day,Week,SemiMonth,Month,Quarter,SemiYear,Year',
            'recurring_day' => 'numeric|min:1|max:31',
            'recurring_day_of_week' => 'numeric|min:1|max:7',
            'cover_fees' => 'boolean',
            'next_payment_date' => 'date',
            'payment_method_id' => [
                'numeric',
                Rule::exists('payment_methods', 'id')->where(function ($query) {
                    $query->where('member_id', member('id'));
                }),
            ],
        ], [
            'amount.min' => __('frontend/api.amount_greater_than_0_dollars'),
            'billing_period.in' => __('frontend/api.error_while_saving_payment_details'),
        ]);

        if ($validator->fails()) {
            return $this->failure($validator->errors()->first());
        }

        if (request()->has('amount')) {
            $subscription->amt = request('amount');
        }

        if (request()->has('billing_period')) {
            $subscription->billing_period = request('billing_period');
        }

        if (request()->has('cover_fees')) {
            $subscription->dcc_enabled_by_customer = request('cover_fees');

            if ($subscription->isDirty(['amt', 'dcc_enabled_by_customer'])
                && (! sys_get('dcc_ai_is_enabled') || request('cover_costs_type') === 'original')) {
                if ($subscription->dcc_enabled_by_customer) {
                    $subscription->dcc_per_order_amount = $subscription->order->dcc_per_order_amount;
                    $subscription->dcc_rate = $subscription->order->dcc_rate;
                    $subscription->dcc_amount = round($subscription->dcc_per_order_amount + ($subscription->amt * $subscription->dcc_rate / 100), 2);
                } else {
                    $subscription->dcc_per_order_amount = 0;
                    $subscription->dcc_rate = 0;
                    $subscription->dcc_amount = 0;
                }
            }
        }

        if (request('cover_costs_type') !== 'original') {
            $subscription->dcc_type = request('cover_costs_type');
        }

        if (sys_get('dcc_ai_is_enabled') && request('cover_costs_type') !== 'original' && $subscription->isDirty(['amt', 'dcc_type'])) {
            $subscription->dcc_enabled_by_customer = (bool) request('cover_costs_type');
            $subscription->dcc_amount = app(DonorCoversCostsService::class)->getCost($subscription->amt, $subscription->dcc_type);
            $subscription->dcc_per_order_amount = 0;
            $subscription->dcc_rate = 0;
        }

        if (request()->has('payment_method_id')) {
            $subscription->payment_method_id = request('payment_method_id');
        }

        if (request()->has('next_payment_date')) {
            $subscription->billing_cycle_anchor = request('next_payment_date');
            $subscription->next_billing_date = request('next_payment_date');
        } elseif (request()->has('change_next_billing_date')) {
            $subscription->next_billing_date = $subscription->getFirstPossibleStartDate(
                'fixed',
                (int) request('recurring_day'),
                (int) request('recurring_day_of_week'),
                null
            );
        }

        $subscription->save();
        // }

        if ($subscription->status === RecurringPaymentProfileStatus::SUSPENDED) {
            $subscription->activateProfile();
        }

        return $this->success(Drop::factory($subscription, 'Subscription'));
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelSubscription(Account $account, $subscriptionId)
    {
        try {
            $subscription = $account->recurringPaymentProfiles()->hashid($subscriptionId)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return $this->failure(__('frontend/api.subscription_not_found'), 404);
        }

        if ($subscription->status === RecurringPaymentProfileStatus::EXPIRED) {
            return $this->failure(__('frontend/api.cannot_cancelled_expired_subscription'));
        }

        if ($subscription->status === RecurringPaymentProfileStatus::CANCELLED) {
            return $this->failure(__('frontend/api.subscription_already_cancelled'));
        }

        if ($subscription->is_locked) {
            return $this->failure(__('frontend/api.cannot_cancelled_locked_subscription'));
        }

        $subscription->cancelProfile(request('cancel_reason'));

        if (request('nps')) {
            $account->nps = request('nps');
            $account->save();
        }

        return $this->success(Drop::factory($subscription, 'Subscription'));
    }
}
