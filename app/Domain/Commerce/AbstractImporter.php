<?php

namespace Ds\Domain\Commerce;

use Closure;
use Ds\Common\ISO3166\ISO3166;
use Ds\Domain\Commerce\Models\PaymentProvider;
use Ds\Events\OrderWasCompleted;
use Ds\Models\Order;
use Ds\Models\OrderItem;
use Ds\Models\Payment;
use Ds\Models\RecurringPaymentProfile;
use Ds\Models\Transaction;
use Ds\Services\PaymentService;
use Ds\Services\TransactionService;
use Illuminate\Support\Facades\App;

abstract class AbstractImporter
{
    /** @var \Ds\Domain\Commerce\Models\PaymentProvider */
    protected $provider;

    /** @var \Ds\Domain\Commerce\AbstractGateway */
    protected $gateway;

    /** @var \Ds\Common\ISO3166\ISO3166 */
    protected $iso3166;

    /** @var \Ds\Services\PaymentService */
    protected $paymentService;

    /** @var \Ds\Services\TransactionService */
    protected $transactionService;

    /** @var int */
    protected $onetimeVariantId;

    /** @var int */
    protected $recurringVariantId;

    /** @var bool */
    protected $dpAutoSync = false;

    /**
     * Create an instance.
     *
     * @param \Ds\Domain\Commerce\Models\PaymentProvider $provider
     */
    public function __construct(
        PaymentProvider $provider,
        ISO3166 $iso3166,
        PaymentService $paymentService,
        TransactionService $transactionService,
        $onetimeVariantId,
        $recurringVariantId = null,
        $dpAutoSync = false
    ) {
        $this->setProvider($provider);
        $this->iso3166 = $iso3166;
        $this->paymentService = $paymentService;
        $this->transactionService = $transactionService;
        $this->onetimeVariantId = $onetimeVariantId;
        $this->recurringVariantId = $recurringVariantId;
        $this->dpAutoSync = $dpAutoSync;
    }

    /**
     * Set the Payment Provider.
     *
     * @param \Ds\Domain\Commerce\Models\PaymentProvider $provider
     */
    protected function setProvider(PaymentProvider $provider)
    {
        $this->provider = $provider;
        $this->gateway = $provider->gateway;
    }

    /**
     * Import a Payment from a gateway.
     *
     * @param mixed $reference
     * @return \Ds\Models\Payment
     */
    abstract public function importPaymentFromGateway($reference): ?Payment;

    /**
     * Create an Order from a Payment.
     *
     * @param \Ds\Models\Payment $payment
     * @param \Closure $updateOrderItem
     * @return \Ds\Models\Order
     */
    protected function createOrderFromPayment(
        Payment $payment,
        string $orderPrefix,
        Closure $updateOrderItem = null
    ): Order {
        $uuid = uuid(function () use ($orderPrefix) {
            return $orderPrefix . mt_rand(10000000, 99999999);
        });

        $order = new Order;
        $order->client_uuid = $uuid;
        $order->is_test = ! $payment->livemode;
        $order->createddatetime = fromUtc($payment->created_at);
        $order->iscomplete = $payment->paid;
        $order->currency_code = $payment->currency;
        $order->functional_currency_code = sys_get('dpo_currency');
        $order->source = 'Legacy Importer';
        $order->payment_method_id = $payment->paymentMethod->id ?? null;
        $order->auth_attempts = 1;
        $order->payment_provider_id = $this->provider->id;
        $order->response_text = $payment->failure_message ?: $payment->status;
        $order->tax_receipt_type = sys_get('tax_receipt_type');
        $order->dp_sync_order = $this->dpAutoSync;
        $order->started_at = fromUtc($payment->created_at);
        $order->created_at = fromUtc($payment->created_at);
        $order->created_by = 1;
        $order->ordered_at = fromUtc($payment->created_at);

        if ($payment->type === 'card') {
            $order->payment_type = 'credit_card';
            $order->billing_name_on_account = $payment->card_name;
            $order->billingcardtype = $payment->card_brand;
            $order->billingcardlastfour = $payment->card_last4;
            $order->billing_card_expiry_month = $payment->card_exp_month ? numeral($payment->card_exp_month)->format('00') : null;
            $order->billing_card_expiry_year = $payment->card_exp_year;
        } elseif ($payment->type === 'bank') {
            $order->payment_type = 'bank_account';
            $order->billing_name_on_account = $payment->bank_account_holder_name;
            $order->billingcardtype = ucwords("checking {$payment->bank_account_holder_type}");
            $order->billingcardlastfour = $payment->bank_last4;
        } else {
            $order->payment_type = $payment->type;
            $order->billingcardtype = $payment->source_type;
        }

        $order->save();

        if ($payment->account) {
            $order->populateMember($payment->account);
        }

        $payment->description = "Payment for Contribution #{$order->client_uuid}";
        $payment->save();

        $payment->orders()->attach($order);

        $item = new OrderItem;
        $item->price = $payment->amount;
        $item->qty = 1;

        $saveItem = function () use ($order, $item) {
            $item->original_price = $item->price;
            $item->productorderid = $order->id;
            $item->productinventoryid = $this->onetimeVariantId;

            if ($item->recurring_frequency && $this->recurringVariantId) {
                $item->productinventoryid = $this->recurringVariantId;
            }

            $item->save();
        };

        if ($updateOrderItem) {
            $updateOrderItem($item, $saveItem);
        }

        $saveItem();

        if ($item->recurring_frequency) {
            $order->recurring_items = 1;
        }

        $order->subtotal = $item->price;
        $order->totalamount = $item->price;
        $order->createddatetime = fromUtc($payment->created_at);
        $order->save();

        if ($payment->paid) {
            $order->confirmationnumber = $payment->reference_number;
            $order->confirmationdatetime = fromUtc($payment->created_at);
            $order->invoicenumber = $order->client_uuid;
            $order->is_processed = true;
            $order->save();

            $order->saveOriginalData();

            $listeners = [
                \Ds\Listeners\Order\StockAdjustments::class,
                \Ds\Listeners\Order\ApplyMemberships::class,
                // \Ds\Listeners\Order\IssueTaxReciept::class,
                // \Ds\Listeners\Order\SendNotificationEmails::class,
                \Ds\Listeners\Order\DonorPerfectSync::class,
                // \Ds\Domain\Webhook\Listeners\OrderCompleted::class,
            ];

            foreach ($listeners as $listener) {
                App::make($listener)->handle(new OrderWasCompleted($order));
            }
        }

        if ($payment->amount_refunded) {
            $order->refunded_at = data_get($payment, 'refunds.0.created_at');
            $order->refunded_amt = data_get($payment, 'refunds.0.amount');
            $order->refunded_auth = data_get($payment, 'refunds.0.reference_number');
            $order->refunded_by = data_get($payment, 'refunds.0.refunded_by_id');
            $order->save();
        }

        return $order;
    }

    /**
     * Create a Transaction from a Payment.
     *
     * @param \Ds\Models\Payment $payment
     * @param \Ds\Models\RecurringPaymentProfile $rpp
     * @param \Ds\Domain\Commerce\Money $amount
     * @return \Ds\Models\Transaction
     */
    protected function createTransactionFromPayment(
        Payment $payment,
        RecurringPaymentProfile $rpp,
        Money $amount = null
    ): Transaction {
        $transaction = $this->transactionService->createTransaction(
            $rpp,
            $payment,
            $amount ?? money($payment->amount, $payment->currency),
            $this->dpAutoSync && sys_get('dp_auto_sync_txns')
        );

        $payment->description = "Payment for Recurring Payment Profile #{$rpp->profile_id}";
        $payment->save();

        if ($payment->amount_refunded) {
            $transaction->refunded_at = data_get($payment, 'refunds.0.created_at');
            $transaction->refunded_amt = data_get($payment, 'refunds.0.amount');
            $transaction->refunded_auth = data_get($payment, 'refunds.0.reference_number');
            $transaction->refunded_by = data_get($payment, 'refunds.0.refunded_by_id');
            $transaction->save();
        }

        return $transaction;
    }
}
