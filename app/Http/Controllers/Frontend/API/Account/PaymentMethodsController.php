<?php

namespace Ds\Http\Controllers\Frontend\API\Account;

use Ds\Domain\Commerce\Models\PaymentProvider;
use Ds\Domain\Theming\Liquid\Drop;
use Ds\Enums\RecurringPaymentProfileStatus;
use Ds\Http\Controllers\Frontend\API\Controller;
use Ds\Models\Member;
use Ds\Models\PaymentMethod;

class PaymentMethodsController extends Controller
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
    public function getPaymentMethods(Member $account)
    {
        return $this->success(Drop::collectionFactory($account->paymentMethods, 'PaymentMethod'));
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function createPaymentMethod(Member $account)
    {
        switch (request('payment_type')) {
            case 'bank_account':
                $provider = PaymentProvider::getBankAccountProvider();
                break;
            case 'paypal':
                $provider = PaymentProvider::getPayPalProvider();
                break;
            default:
                $provider = PaymentProvider::getCreditCardProvider();
        }

        $paymentMethod = new PaymentMethod;
        $paymentMethod->member_id = $account->id;
        $paymentMethod->payment_provider_id = $provider->id;
        $paymentMethod->status = 'PENDING';
        $paymentMethod->currency_code = sys_get('dpo_currency');
        $paymentMethod->billing_first_name = request('billing_first_name', $account->bill_first_name);
        $paymentMethod->billing_last_name = request('billing_last_name', $account->bill_last_name);
        $paymentMethod->billing_email = request('billing_email', $account->bill_email);
        $paymentMethod->billing_address1 = request('billing_address1', $account->bill_address_01);
        $paymentMethod->billing_address2 = request('billing_address2', $account->bill_address_02);
        $paymentMethod->billing_city = request('billing_city', $account->bill_city);
        $paymentMethod->billing_state = request('billing_province_code', $account->bill_state);
        $paymentMethod->billing_postal = request('billing_zip', $account->bill_zip);
        $paymentMethod->billing_country = request('billing_country_code', $account->bill_country);
        $paymentMethod->billing_phone = request('billing_phone', $account->bill_phone);
        $paymentMethod->credential_on_file = $provider->usingCredentialOnFile();
        $paymentMethod->save();

        return $this->success(Drop::factory($paymentMethod, 'PaymentMethod'));
    }

    /**
     * @return \Ds\Domain\Commerce\Responses\UrlResponse
     */
    public function tokenizePaymentMethod(Member $account, $id)
    {
        $context = request('context');
        $paymentMethod = $account->paymentMethods()->findOrFail($id);

        return $paymentMethod->paymentProvider->getSourceTokenUrl(
            $paymentMethod,
            secure_site_url("/account/payment-methods/{$paymentMethod->id}/connect?rt_context={$context}"),
            secure_site_url("/account/payment-methods/{$paymentMethod->id}")
        );
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function connectPaymentMethod(Member $account, $id)
    {
        $paymentMethod = $account->paymentMethods()->findOrFail($id);

        if ($paymentMethod->status === 'PENDING') {
            $paymentMethod->updateWithTransactionResponse(
                $paymentMethod->paymentProvider->createSourceToken($paymentMethod)
            );

            member_notify_updated_profile($account->id, null, [
                __('frontend/api.payment_method_added_to_profile', [
                    'account_type' => $paymentMethod->account_type,
                    'account_last_four' => $paymentMethod->account_last_four,
                ]),
            ]);
        }

        return $this->success();
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPaymentMethod(Member $account, $id)
    {
        $paymentMethod = $account->paymentMethods()->findOrFail($id);

        return $this->success(Drop::factory($paymentMethod, 'PaymentMethod'));
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function setDefaultPaymentMethod(Member $account, $id)
    {
        $paymentMethod = $account->paymentMethods()->findOrFail($id);

        $paymentMethod->useAsDefaultPaymentMethod();

        return $this->success(Drop::factory($paymentMethod, 'PaymentMethod'));
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function useForSubscriptions(Member $account, $id)
    {
        $paymentMethod = $account->paymentMethods()->findOrFail($id);

        $subscriptions = $account->recurringPaymentProfiles()
            ->where('is_locked', false)
            ->whereIn('profile_id', (array) request('subscriptions'))
            ->get();

        foreach ($subscriptions as $subscription) {
            $subscription->payment_method_id = $paymentMethod->id;
            $subscription->is_manual = false;
            $subscription->save();

            if ($subscription->status === RecurringPaymentProfileStatus::SUSPENDED) {
                $subscription->activateProfile();
            }
        }

        return $this->success(Drop::factory($paymentMethod, 'PaymentMethod'));
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function deletePaymentMethod(Member $account, $id)
    {
        $paymentMethod = $account->paymentMethods()->findOrFail($id);

        if ($paymentMethod->use_as_default) {
            $alternativePaymentMethod = $account->paymentMethods()
                ->where('id', '!=', $paymentMethod->id)
                ->active()
                ->first();

            if ($alternativePaymentMethod) {
                $paymentMethod->use_as_default = false;
                $paymentMethod->save();
                $alternativePaymentMethod->use_as_default = true;
                $alternativePaymentMethod->save();
            }
        } else {
            $alternativePaymentMethod = $account->defaultPaymentMethod;
        }

        if (! $alternativePaymentMethod && ! volt_has_account_feature('delete-default-payment-method')) {
            return $this->failure(__('frontend/api.add_another_payment_method_before_removing_current'));
        }

        foreach ($paymentMethod->recurringPaymentProfiles as $profile) {
            if ($alternativePaymentMethod) {
                $profile->payment_method_id = $alternativePaymentMethod->id;
            } else {
                $profile->payment_method_id = null;
                if ($profile->status === RecurringPaymentProfileStatus::ACTIVE && ! $profile->is_manual) {
                    $profile->status = RecurringPaymentProfileStatus::SUSPENDED;
                }
            }

            $profile->save();
        }

        $paymentMethod->delete();

        member_notify_updated_profile($account->id, null, [
            __('frontend/api.payment_method_removed_to_profile', [
                'account_type' => $paymentMethod->account_type,
                'account_last_four' => $paymentMethod->account_last_four,
            ]),
        ]);

        return $this->success();
    }
}
