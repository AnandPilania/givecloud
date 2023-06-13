<?php

namespace Ds\Services;

use Ds\Domain\Commerce\Responses\TransactionResponse;
use Ds\Enums\PaymentType;
use Ds\Models\Order;
use Ds\Models\Payment;

class OrderService
{
    /** @var \Ds\Services\PaymentService */
    private $paymentService;

    /**
     * Create an instance.
     *
     * @param \Ds\Services\PaymentService $paymentService
     */
    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Create a Payment from a Transaction Response.
     *
     * @param \Ds\Models\Order $order
     * @param \Ds\Domain\Commerce\Responses\TransactionResponse $res
     * @return \Ds\Models\Payment|null
     */
    public function createPaymentFromTransactionResponse(Order $order, TransactionResponse $res): ?Payment
    {
        if (empty($order->totalamount) || $order->totalamount < 0) {
            return null;
        }

        $provider = $res->getProvider();

        $payment = new Payment;
        $payment->livemode = ! $provider->test_mode;
        $payment->amount = $order->totalamount;
        $payment->amount_refunded = 0;
        $payment->currency = $order->currency_code ?? sys_get('dpo_currency');
        $payment->paid = false;
        $payment->reference_number = $res->getTransactionId() ?: null;
        $payment->description = "Payment for Order #{$order->client_uuid}";
        $payment->source_account_id = $order->member_id;
        $payment->source_payment_method_id = $order->paymentMethod->id ?? null;
        $payment->gateway_type = $provider->provider;
        $payment->gateway_customer = $res->getCustomerRef() ?: null;
        $payment->gateway_source = $res->getSourceToken() ?: null;
        $payment->ip_address = $res->getIpAddress() ?: null;
        $payment->application_fee_billing = (bool) sys_get('dcc_stripe_application_fee_billing');
        $payment->application_fee_amount = $res->getApplicationFeeAmount();
        $payment->stripe_payment_intent = $res->getStripePaymentIntent();

        $payment->type = PaymentType::UNKNOWN;
        if ($payment->gateway_type === 'gocardless') {
            $payment->type = PaymentType::BANK;
        } elseif ($payment->gateway_type === 'paypalcheckout' || $payment->gateway_type === 'paypalexpress') {
            $payment->type = PaymentType::PAYPAL;
        } elseif ($res->getCardExpiry()) {
            $payment->type = PaymentType::CARD;
        } elseif ($res->getAchType()) {
            $payment->type = PaymentType::BANK;
        } else {
            switch (strtolower($order->payment_type_formatted)) {
                case 'ach':         $payment->type = PaymentType::BANK; break;
                case 'cash':        $payment->type = PaymentType::CASH; break;
                case 'cc':          $payment->type = PaymentType::CARD; break;
                case 'check':       $payment->type = PaymentType::CHEQUE; break;
                case 'credit card': $payment->type = PaymentType::CARD; break;
                case 'other':       $payment->type = PaymentType::UNKNOWN; break;
                case 'paypal':      $payment->type = PaymentType::PAYPAL; break;
            }

            switch ($order->payment_type) {
                case 'credit_card':  $payment->type = PaymentType::CARD; break;
                case 'bank_account': $payment->type = PaymentType::BANK; break;
                case 'paypal':       $payment->type = PaymentType::PAYPAL; break;
                case 'wallet_pay':   $payment->type = PaymentType::CARD; break;
            }
        }

        if ($res->isCompleted()) {
            $payment->status = 'succeeded';
            $payment->outcome = 'authorized';
        } else {
            $payment->status = 'failed';
            $payment->outcome = 'issuer_declined';
        }

        if ($payment->type === PaymentType::CARD) {
            $this->paymentService->handleCvvResponse($payment, $res->getCVV2Code() ?? '');
            $this->paymentService->handleAvsResponse($payment, $res->getAVSCode() ?? '');

            if ($payment->status === 'failed') {
                $this->paymentService->handleResponseText($payment, $res->getResponseText() ?? '');
            }

            $payment->card_funding = 'unknown';
            $payment->card_brand = $res->getAccountType() ?: null;
            $payment->card_last4 = $res->getAccountLastFour() ?: null;
            $payment->card_exp_month = $res->getCardExpiryMonth() ?: null;
            $payment->card_exp_year = $res->getCardExpiryYear() ?: null;
            $payment->card_entry_type = $res->getCardEntryType() ?: 'card_not_present';
            $payment->card_verification = $res->getCardVerification() ?: null;
            $payment->card_wallet = $res->getCardWallet() ?: null;
            $payment->card_country = $order->billingcountry ?: null;
            $payment->card_name = trim("{$order->billing_first_name} {$order->billing_last_name}") ?: null;
            $payment->card_address_line1 = $order->billingaddress1 ?: null;
            $payment->card_address_line2 = $order->billingaddress2 ?: null;
            $payment->card_address_city = $order->billingcity ?: null;
            $payment->card_address_state = $order->billingstate ?: null;
            $payment->card_address_zip = $order->billingzip ?: null;
            $payment->card_address_country = $order->billingcountry ?: null;
        } elseif ($payment->type === PaymentType::BANK) {
            if ($payment->status === 'failed') {
                $this->paymentService->handleResponseText($payment, $res->getResponseText() ?? '');
            } else {
                $payment->status = 'pending';
            }

            $payment->bank_last4 = $res->getAccountLastFour() ?: null;
            $payment->bank_routing_number = $res->getAchRouting() ?: null;
            $payment->bank_account_type = $res->getAchType() ?: null;
            $payment->bank_account_holder_type = $res->getAchEntity() ?: null;
            $payment->bank_account_holder_name = trim("{$order->billing_first_name} {$order->billing_last_name}") ?: null;
        } elseif ($payment->type === PaymentType::CHEQUE) {
            $payment->amount = $order->check_amt;
            $payment->cheque_number = $order->check_number;
            $payment->cheque_date = $order->check_date;
        } elseif ($payment->type === PaymentType::CASH) {
            $payment->amount = $order->cash_received;
        }

        if ($payment->status !== 'failed') {
            $payment->captured = true;
            $payment->captured_at = now();
        }

        if ($payment->status === 'succeeded' && $payment->captured) {
            $payment->paid = true;
        }

        $payment->payment_audit_log = 'json:' . json_encode($res);
        $payment->save();

        $payment->orders()->attach($order);

        if (optional($payment->paymentProvider)->provider === 'stripe' && $payment->captured) {
            $payment->paymentProvider->updateStripeFeesForPayment($payment);
        }

        $order->createOrUpdateContribution();

        return $payment;
    }
}
