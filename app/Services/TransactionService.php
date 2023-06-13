<?php

namespace Ds\Services;

use Ds\Domain\Commerce\Money;
use Ds\Domain\Commerce\Responses\TransactionResponse;
use Ds\Domain\Shared\Exceptions\MessageException;
use Ds\Models\Payment;
use Ds\Models\RecurringPaymentProfile;
use Ds\Models\Refund;
use Ds\Models\Transaction;
use Ds\Repositories\TransactionRepository;
use Illuminate\Support\Str;

class TransactionService
{
    /** @var \Ds\Services\PaymentService */
    private $paymentService;

    /** @var \Ds\Repositories\TransactionRepository */
    private $transactionRepository;

    /**
     * Create an instance.
     *
     * @param \Ds\Services\PaymentService $paymentService
     * @param \Ds\Repositories\TransactionRepository $transactionRepository
     */
    public function __construct(PaymentService $paymentService, TransactionRepository $transactionRepository)
    {
        $this->paymentService = $paymentService;
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * Create a Payment.
     *
     * @param \Ds\Models\Transaction $transaction
     * @param \Ds\Domain\Commerce\Responses\TransactionResponse $res
     * @return \Ds\Models\Payment
     */
    public function createPayment(Transaction $transaction, ?TransactionResponse $res = null): Payment
    {
        if (empty($transaction->amt)) {
            throw new MessageException("Can't create a payment for zero dollars.");
        }

        if ($transaction->amt < 0) {
            throw new MessageException("Can't make negative payments.");
        }

        if ($transaction->payments()->count()) {
            throw new MessageException('Transaction already has a payment.');
        }

        if (preg_match('/sending charge.*?({.*?})\n.*finished sending charge/s', $transaction->transaction_log, $matches)) {
            $json = json_decode($matches[1], true);
        } else {
            $json = null;
        }

        $payment = new Payment;
        $payment->livemode = ! ($transaction->recurringPaymentProfile->order->is_test ?? true);
        $payment->status = ($transaction->payment_status === 'Completed') ? 'succeeded' : 'failed';
        $payment->amount = $transaction->amt;
        $payment->amount_refunded = 0;
        $payment->currency = $transaction->currency_code;
        $payment->paid = false;
        $payment->reference_number = $transaction->transaction_id ?: null;
        $payment->description = "Payment for Transaction #{$transaction->id}";
        $payment->source_account_id = $transaction->recurringPaymentProfile->member_id ?? null;
        $payment->source_payment_method_id = $transaction->payment_method_id;
        $payment->gateway_source = $transaction->paymentMethod->token ?? $json['customer_vault_id'] ?? null;
        $payment->application_fee_billing = (bool) sys_get('dcc_stripe_application_fee_billing');
        $payment->application_fee_amount = optional($res)->getApplicationFeeAmount();
        $payment->stripe_payment_intent = optional($res)->getStripePaymentIntent();
        $payment->platform_fee_type = $transaction->recurringPaymentProfile->platform_fee_type;
        $payment->payment_audit_log = $json ? 'json;' . json_encode($json) : ($transaction->reason_code ?: null);

        switch (strtolower($transaction->payment_method_type)) {
            case 'cash':        $payment->type = 'cash'; $payment->gateway_type = 'offline'; break;
            case 'check':       $payment->type = 'cheque'; $payment->gateway_type = 'offline'; break;
            case 'gocardless':  $payment->type = 'bank'; $payment->gateway_type = 'gocardless'; break;
            case 'other':       $payment->type = 'unknown'; $payment->gateway_type = 'offline'; break;
            case 'paypal':      $payment->type = 'paypal'; $payment->gateway_type = 'paypalexpress'; break;
            default:
                if ($this->paymentService->validCardBrand($transaction->paymentMethod->account_type ?? '')) {
                    $payment->gateway_type = 'safesave';
                    $payment->type = 'card';
                } elseif ($this->paymentService->validBankAccountHolderType($transaction->paymentMethod->account_type ?? '')) {
                    $payment->gateway_type = 'safesave';
                    $payment->type = 'bank';
                } elseif (strtolower($transaction->paymentMethod->account_type ?? '') === 'paypal') {
                    $payment->gateway_type = 'paypalexpress';
                    $payment->type = 'paypal';
                } elseif ($json['customer_vault_id'] ?? false) {
                    $payment->gateway_type = 'safesave';
                    $payment->type = 'card';
                } else {
                    $payment->gateway_type = 'unknown';
                    $payment->type = 'unknown';
                }
        }

        if (isset($transaction->recurringPaymentProfile->paypal_subscription_id)) {
            $payment->gateway_type = 'paypalexpress';
            $payment->type = 'paypal';
        }

        if (isset($transaction->paymentMethod->paymentProvider)) {
            $payment->gateway_type = $transaction->paymentMethod->paymentProvider->provider;

            if (Str::contains($payment->gateway_type, 'paypal')) {
                $payment->type = 'paypal';
            }
        }

        if ($payment->type === 'card') {
            $payment->card_funding = 'unknown';
            $payment->card_brand = ($transaction->paymentMethod->account_type ?? null) ?: null;
            $payment->card_last4 = ($transaction->paymentMethod->account_last_four ?? substr($transaction->payment_method_desc, -4)) ?: null;
            $payment->card_exp_month = fromUtcFormat($transaction->paymentMethod->cc_expiry ?? null, 'm') ?: null;
            $payment->card_exp_year = fromUtcFormat($transaction->paymentMethod->cc_expiry ?? null, 'Y') ?: null;
            $payment->card_country = ($transaction->paymentMethod->billing_country ?? null) ?: null;
            $payment->card_wallet = ($transaction->paymentMethod->cc_wallet ?? null) ?: null;
            $payment->card_name = $transaction->paymentMethod ? (trim("{$transaction->paymentMethod->billing_first_name} {$transaction->paymentMethod->billing_last_name}") ?: null) : null;
            $payment->card_address_line1 = ($transaction->paymentMethod->billing_address1 ?? null) ?: null;
            $payment->card_address_line2 = ($transaction->paymentMethod->billing_address2 ?? null) ?: null;
            $payment->card_address_city = ($transaction->paymentMethod->billing_city ?? null) ?: null;
            $payment->card_address_state = ($transaction->paymentMethod->billing_state ?? null) ?: null;
            $payment->card_address_zip = ($transaction->paymentMethod->billing_postal ?? null) ?: null;
            $payment->card_address_country = ($transaction->paymentMethod->billing_country ?? null) ?: null;

            if (! $transaction->paymentMethod && isset($transaction->recurringPaymentProfile->order) && $payment->card_last4 === $transaction->recurringPaymentProfile->order->billingcardlastfour) {
                $payment->card_brand = ($transaction->recurringPaymentProfile->order->billingcardtype ?? null) ?: null;
                $payment->card_exp_month = ($transaction->recurringPaymentProfile->order->billing_card_expiry_month ?? null) ?: null;
                $payment->card_exp_year = ($transaction->recurringPaymentProfile->order->billing_card_expiry_year ?? null) ?: null;
                $payment->card_country = ($transaction->recurringPaymentProfile->order->billingcountry ?? null) ?: null;
                $payment->card_name = isset($transaction->recurringPaymentProfile->order) ? (trim("{$transaction->recurringPaymentProfile->order->billing_first_name} {$transaction->recurringPaymentProfile->order->billing_last_name}") ?: null) : null;
                $payment->card_address_line1 = ($transaction->recurringPaymentProfile->order->billingaddress1 ?? null) ?: null;
                $payment->card_address_line2 = ($transaction->recurringPaymentProfile->order->billingaddress2 ?? null) ?: null;
                $payment->card_address_city = ($transaction->recurringPaymentProfile->order->billingcity ?? null) ?: null;
                $payment->card_address_state = ($transaction->recurringPaymentProfile->order->billingstate ?? null) ?: null;
                $payment->card_address_zip = ($transaction->recurringPaymentProfile->order->billingzip ?? null) ?: null;
                $payment->card_address_country = ($transaction->recurringPaymentProfile->order->billingcountry ?? null) ?: null;
            }

            $this->paymentService->handleCvvResponse($payment, $json['cvvresponse'] ?? $json['cvv_response'] ?? $json['cvv_code'] ?? '');
            $this->paymentService->handleAvsResponse($payment, $json['avsresponse'] ?? $json['avs_response'] ?? $json['avs_code'] ?? '');
            $this->paymentService->handleResponseText($payment, $transaction->reason_code);
        }

        if ($payment->type === 'bank') {
            if (Str::startsWith($payment->reference_number, 'PM')) {
                $payment->gateway_type = 'gocardless';
            }

            $payment->status = 'pending';
            $payment->bank_last4 = ($transaction->paymentMethod->account_last_four ?? null) ?: null;
            $payment->bank_account_holder_name = $transaction->paymentMethod ? (trim("{$transaction->paymentMethod->billing_first_name} {$transaction->paymentMethod->billing_last_name}") ?: null) : null;
            $payment->bank_account_holder_type = ($transaction->paymentMethod->ach_entity_type ?? null) ?: null;
            $payment->bank_account_type = ($transaction->paymentMethod->ach_entity_type ?? null) ?: null;

            $this->paymentService->handleResponseText($payment, $transaction->reason_code);

            if ($payment->status === 'succeeded') {
                $payment->status = 'pending';
            }
        }

        if ($payment->type === 'cheque' || $payment->type === 'cash' || strtolower($transaction->payment_method_type) === 'other') {
            $payment->description = $transaction->reason_code ?: $payment->description;
        }

        if ($payment->status !== 'failed') {
            $payment->captured = true;
            $payment->captured_at = $transaction->order_time;
        }

        if ($payment->status === 'succeeded' && $payment->captured) {
            $payment->paid = true;
        }

        $payment->created_at = $transaction->order_time;
        $payment->updated_at = $transaction->order_time;
        $payment->save();

        if ($transaction->is_refunded) {
            $refund = new Refund;
            $refund->status = 'succeeded';
            $refund->reference_number = $transaction->refunded_auth;
            $refund->amount = $transaction->refunded_amt;
            $refund->currency = $transaction->currency_code;
            $refund->reason = 'requested_by_customer';
            $refund->refunded_by_id = $transaction->refunded_by ?? 1;
            $refund->created_at = $transaction->refunded_at;
            $refund->updated_at = $transaction->refunded_at;

            $payment->refunds()->save($refund);
        }

        // special case only applicable to OurRescue
        if ($transaction->payment_status === 'Voided') {
            if (preg_match('/voiding transaction.*?(2018.*?) -- ({.*?})\n.*finished voiding transaction/s', $transaction->transaction_log, $matches)) {
                $date = fromUtc($matches[1]);
                $json = json_decode($matches[2], true);
            } else {
                $date = $transaction->order_time;
                $json = null;
            }

            $refund = new Refund;
            $refund->status = 'succeeded';
            $refund->reference_number = $payment->reference_number;
            $refund->amount = $payment->amount;
            $refund->currency = $payment->currency;
            $refund->reason = 'duplicate';
            $refund->refunded_by_id = 1;
            $refund->refund_audit_log = 'json;' . json_encode($json);
            $refund->created_at = $date;
            $refund->updated_at = $date;

            $payment->refunds()->save($refund);
        }

        $payment->recurringPaymentProfiles()->attach($transaction->recurringPaymentProfile, ['transaction_id' => $transaction->id]);

        if (optional($payment->paymentProvider)->provider === 'stripe' && $payment->captured) {
            $payment->paymentProvider->updateStripeFeesForPayment($payment);
        }

        return $payment;
    }

    /**
     * Create a transaction for an RPP payment.
     *
     * @param \Ds\Models\RecurringPaymentProfile $rpp
     * @param \Ds\Models\Payment $payment
     * @param \Ds\Domain\Commerce\Money $amount
     * @param bool $dpAutoSync
     * @return \Ds\Models\Transaction
     */
    public function createTransaction(
        RecurringPaymentProfile $rpp,
        Payment $payment,
        Money $amount = null,
        $dpAutoSync = null
    ): Transaction {
        $transaction = $rpp->createTransaction(false, $amount);

        if ($dpAutoSync !== null) {
            $transaction->dp_auto_sync = $dpAutoSync;
        }

        // Rewrite the reason code so the transaction handler recognizes
        // the transaction as a duplicate and doesn't suspend the profile
        if ($payment->failure_code === 'duplicate_transaction') {
            $transaction->reason_code = 'Duplicate transaction';
        } else {
            $transaction->reason_code = $payment->failure_message ?? ucwords($payment->status);
        }

        $transaction->transaction_id = $payment->reference_number;
        $transaction->order_time = fromUtc($payment->created_at);
        $transaction->payment_method_id = $payment->source_payment_method_id;
        $transaction->payment_method_type = $payment->gateway_type;
        $transaction->payment_method_desc = $payment->paymentMethod->account_number ?? '';
        $transaction->payment_status = ($payment->status === 'failed') ? 'Denied' : 'Completed';
        $transaction->save();

        $transaction->payments()->attach($payment, ['recurring_payment_profile_id' => $rpp->id]);

        $this->transactionRepository->handleTransaction($rpp, $transaction, false);

        return $transaction;
    }
}
