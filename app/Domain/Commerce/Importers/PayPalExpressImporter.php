<?php

namespace Ds\Domain\Commerce\Importers;

use Ds\Domain\Commerce\AbstractImporter;
use Ds\Domain\Commerce\Gateways\PayPalExpressGateway;
use Ds\Domain\Commerce\Models\PaymentProvider;
use Ds\Models\AccountType;
use Ds\Models\Member as Account;
use Ds\Models\Order;
use Ds\Models\OrderItem;
use Ds\Models\Payment;
use Ds\Models\RecurringPaymentProfile;
use Ds\Models\Refund;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use InvalidArgumentException;
use PayPal\EBLBaseComponents\PaymentTransactionSearchResultType;
use PayPal\EBLBaseComponents\PaymentTransactionType;
use PayPal\EBLBaseComponents\SubscriptionInfoType;

class PayPalExpressImporter extends AbstractImporter
{
    /** @var \Ds\Domain\Commerce\Gateways\PayPalExpressGateway */
    protected $gateway;

    /**
     * Set the Payment Provider.
     *
     * @param \Ds\Domain\Commerce\Models\PaymentProvider $provider
     */
    protected function setProvider(PaymentProvider $provider)
    {
        parent::setProvider($provider);

        if (! $this->gateway || ! ($provider->gateway instanceof PayPalExpressGateway)) {
            throw new InvalidArgumentException('PayPalExpressImporter requires a PayPalExpressGateway');
        }
    }

    /**
     * Import a Payment from a gateway.
     *
     * @param mixed $reference
     * @return \Ds\Models\Payment
     */
    public function importPaymentFromGateway($reference): ?Payment
    {
        $searchResult = null;
        $paymentTransaction = null;

        if (is_array($reference)) {
            $searchResult = Arr::get($reference, 0);
            $paymentTransaction = Arr::get($reference, 1);
        } elseif (is_string($reference)) {
            $paymentTransaction = $this->gateway->getTransaction($reference);
        } elseif (is_object($reference) && $reference instanceof PaymentTransactionType) {
            $paymentTransaction = $reference;
        } elseif (is_object($reference) && $reference instanceof PaymentTransactionSearchResultType) {
            $searchResult = $reference;
        } else {
            throw new InvalidArgumentException('PayPalExpressImporter expects a referenceNumber, PaymentTransactionType or PaymentTransactionSearchResultType');
        }

        if ($searchResult) {
            if (! in_array($searchResult->Type, ['Payment', 'Recurring Payment', 'Refund'])) {
                return null;
            }

            if (Str::startsWith($searchResult->TransactionID, 'I-')) {
                $this->updateRecurringPaymentProfileFromSearchResult($searchResult);

                return null;
            }

            if (empty($paymentTransaction)) {
                $paymentTransaction = $this->gateway->getTransaction($searchResult->TransactionID);
            }
        }

        if ($paymentTransaction) {
            return $this->importPaymentFromPaymentTransaction($paymentTransaction);
        }

        return null;
    }

    /**
     * Import a Payment from a Payment Transaction.
     *
     * @param \PayPal\EBLBaseComponents\PaymentTransactionType $paymentTransaction
     * @return \Ds\Models\Payment|null
     */
    private function importPaymentFromPaymentTransaction(PaymentTransactionType $paymentTransaction): ?Payment
    {
        if ($paymentTransaction->PaymentInfo->TransactionType === 'refund') {
            $refund = Refund::query()
                ->where('reference_number', $paymentTransaction->PaymentInfo->TransactionID)
                ->first();

            if ($refund) {
                return $refund->payment;
            }

            return $this->createRefundFromPaymentTransaction($paymentTransaction);
        }

        $payment = Payment::query()
            ->where('reference_number', $paymentTransaction->PaymentInfo->TransactionID)
            ->first();

        if ($payment) {
            return $payment;
        }

        if (empty($paymentTransaction->PaymentItemInfo->Subscription->SubscriptionID)) {
            $account = $this->findOrCreateAccount($paymentTransaction);
            $payment = $this->createPaymentFromPaymentTransaction($paymentTransaction, $account);

            $this->createOrderFromPayment($payment, 'PP');
        } else {
            $rpp = $this->findRecurringPaymentProfileFromSubscriptionId($paymentTransaction->PaymentItemInfo->Subscription->SubscriptionID);
            $payment = $this->createPaymentFromPaymentTransaction($paymentTransaction, $rpp->member ?? null);

            if ($rpp) {
                $this->createTransactionFromPayment($payment, $rpp);
            } else {
                $this->createOrderFromPaymentTransaction($paymentTransaction, $payment);
            }
        }

        return $payment;
    }

    /**
     * Find the Recurring Payment Profile for a given Subscription ID.
     *
     * @param string $subscriptionId
     * @return \Ds\Models\RecurringPaymentProfile|null
     */
    private function findRecurringPaymentProfileFromSubscriptionId(string $subscriptionId): ?RecurringPaymentProfile
    {
        return RecurringPaymentProfile::query()
            ->where('is_manual', true)
            ->where('is_locked', true)
            ->where('paypal_subscription_id', $subscriptionId)
            ->first();
    }

    /**
     * Create an Order from a Payment Transaction.
     *
     * @param \PayPal\EBLBaseComponents\PaymentTransactionType $paymentTransaction
     * @param \Ds\Models\Payment $payment
     * @return \Ds\Models\Order
     */
    private function createOrderFromPaymentTransaction(
        PaymentTransactionType $paymentTransaction,
        Payment $payment
    ): Order {
        return $this->createOrderFromPayment(
            $payment,
            'PP',
            function (OrderItem $item, $save) use ($paymentTransaction, $payment) {
                $item->price = $payment->amount;
                $item->recurring_amount = $item->price;
                $item->recurring_with_initial_charge = true;
                $item->recurring_day = fromUtc($payment->created_at)->day;

                // The SubscriptionTermsType appears to always be blank so
                // we make the assumption that all subscriptions are monthly
                $item->recurring_frequency = 'monthly';
                $save();

                $this->createRecurringPaymentProfileFromSubscription(
                    $paymentTransaction->PaymentItemInfo->Subscription,
                    $item,
                    $payment
                );
            }
        );
    }

    /**
     * Update a Recurring Payment Profile from a Search Result.
     *
     * @param \PayPal\EBLBaseComponents\PaymentTransactionSearchResultType $searchResult
     * @return \Ds\Models\RecurringPaymentProfile
     */
    private function updateRecurringPaymentProfileFromSearchResult(
        PaymentTransactionSearchResultType $searchResult
    ): ?RecurringPaymentProfile {
        $statuses = [
            'Completed' => 'Active',
            'Updated' => 'Active',
            'Canceled' => 'Cancelled',
            'Suspended' => 'Suspended',
            'Expired' => 'Suspended',
            'Reactivated' => 'Active',
        ];

        if (empty($statuses[$searchResult->Status])) {
            return null;
        }

        $rpp = $this->findRecurringPaymentProfileFromSubscriptionId($searchResult->TransactionID);

        if ($rpp) {
            $rpp->status = $statuses[$searchResult->Status];

            if ($rpp->status === 'Cancelled') {
                $rpp->final_payment_due_date = fromUtc($searchResult->Timestamp);
            } else {
                $rpp->final_payment_due_date = null;
            }

            return tap($rpp)->save();
        }

        return null;
    }

    /**
     * Create a Recurring Payment Profile from a Subscription.
     *
     * @param \PayPal\EBLBaseComponents\SubscriptionInfoType $subscription
     * @param \Ds\Models\OrderItem $item
     * @param \Ds\Models\Payment $payment
     * @return \Ds\Models\RecurringPaymentProfile
     */
    private function createRecurringPaymentProfileFromSubscription(
        SubscriptionInfoType $subscription,
        OrderItem $item,
        Payment $payment
    ): RecurringPaymentProfile {
        $rpp = new RecurringPaymentProfile;
        $rpp->status = 'Active';
        $rpp->is_manual = true;
        $rpp->is_locked = true;
        $rpp->member_id = $payment->account->id;
        $rpp->paypal_subscription_id = $subscription->SubscriptionID;
        $rpp->subscriber_name = $payment->account->display_name;
        $rpp->profile_start_date = fromUtc($payment->created_at);
        $rpp->profile_reference = $item->order->client_uuid;
        $rpp->description = $item->description;
        $rpp->transaction_type = 'Donation';
        $rpp->billing_period = $item->recurring_frequency;
        $rpp->amt = $item->recurring_amount;
        $rpp->currency_code = $payment->currency;
        $rpp->init_amt = $item->total;
        $rpp->billing_cycle_anchor = fromUtc($payment->created_at);
        $rpp->next_billing_date = fromUtc($payment->created_at)->addMonthWithoutOverflow();
        $rpp->num_cycles_completed = 1;
        $rpp->last_payment_date = fromUtc($payment->created_at);
        $rpp->last_payment_amt = $rpp->init_amt;
        $rpp->aggregate_amount = $rpp->init_amt;
        $rpp->productorder_id = $item->order->id;
        $rpp->productorderitem_id = $item->id;
        $rpp->productinventory_id = $item->variant->id;
        $rpp->product_id = $item->variant->productid;
        $rpp->save();

        return $rpp;
    }

    /**
     * Find or create an Account from a PayPal Payment Transaction.
     *
     * @param \PayPal\EBLBaseComponents\PaymentTransactionType $paymentTransaction
     * @return \Ds\Models\Member
     */
    private function findOrCreateAccount(PaymentTransactionType $paymentTransaction)
    {
        $account = Account::query()
            ->where('paypal_payer_id', $paymentTransaction->PayerInfo->PayerID)
            ->orWhere(function ($query) use ($paymentTransaction) {
                $query->whereNull('paypal_payer_id');
                $query->whereRaw('(email = ? or bill_email = ?)', [
                    $paymentTransaction->PayerInfo->Payer,
                    $paymentTransaction->PayerInfo->Payer,
                ]);
            })->first();

        if (empty($account)) {
            $account = $this->createAccountFromPaymentTransaction($paymentTransaction);
        }

        $account->paypal_payer_id = $paymentTransaction->PayerInfo->PayerID;
        $account->save();

        return $account;
    }

    /**
     * Create an Account from a PayPal Payment Transaction.
     *
     * @param \PayPal\EBLBaseComponents\PaymentTransactionType $paymentTransaction
     * @return \Ds\Models\Member
     */
    private function createAccountFromPaymentTransaction(PaymentTransactionType $paymentTransaction): Account
    {
        $account = new Account;
        $account->first_name = $paymentTransaction->PayerInfo->PayerName->FirstName;
        $account->last_name = $paymentTransaction->PayerInfo->PayerName->LastName;
        $account->bill_first_name = $account->first_name;
        $account->bill_last_name = $account->last_name;
        $account->bill_organization_name = null;
        $account->bill_email = $paymentTransaction->PayerInfo->Payer;
        $account->bill_address_01 = $paymentTransaction->PayerInfo->Address->Street1;
        $account->bill_address_02 = $paymentTransaction->PayerInfo->Address->Street2;
        $account->bill_city = $paymentTransaction->PayerInfo->Address->CityName;
        $account->bill_state = $paymentTransaction->PayerInfo->Address->StateOrProvince;
        $account->bill_zip = $paymentTransaction->PayerInfo->Address->PostalCode;
        $account->bill_country = $paymentTransaction->PayerInfo->Address->Country ?? $paymentTransaction->PayerInfo->PayerCountry;
        $account->bill_phone = $paymentTransaction->PayerInfo->Address->Phone;
        $account->is_active = true;
        $account->paypal_payer_id = $paymentTransaction->PayerInfo->PayerID;
        $account->account_type_id = data_get(AccountType::default()->first(), 'id', 1);
        $account->created_at = fromUtc($paymentTransaction->PaymentInfo->PaymentDate);

        if (Account::where('email', $account->bill_email)->doesntExist()) {
            $account->email = $account->bill_email;
        }

        $account->setDisplayName();
        $account->save();

        return $account;
    }

    /**
     * Create a Payment from a PayPal Payment Transaction.
     *
     * @param \PayPal\EBLBaseComponents\PaymentTransactionType $paymentTransaction
     * @return \Ds\Models\Payment
     */
    private function createPaymentFromPaymentTransaction(
        PaymentTransactionType $paymentTransaction,
        Account $account = null
    ): Payment {
        $statuses = [
            'None' => 'failed',
            'Canceled-Reversal' => 'succeeded',
            'Completed' => 'succeeded',
            'Denied' => 'failed',
            'Expired' => 'failed',
            'Failed' => 'failed',
            'In-Progress' => 'pending',
            'Partially-Refunded' => 'failed',
            'Pending' => 'pending',
            'Refunded' => 'succeeded',
            'Reversed' => 'succeeded',
            'Processed' => 'succeeded',
            'Voided' => 'succeeded',
        ];

        $payment = new Payment;
        $payment->livemode = ! $this->provider->test_mode;
        $payment->type = 'paypal';
        $payment->status = $statuses[$paymentTransaction->PaymentInfo->PaymentStatus] ?? 'failed';
        $payment->amount = $paymentTransaction->PaymentInfo->GrossAmount->value;
        $payment->currency = $paymentTransaction->PaymentInfo->GrossAmount->currencyID;
        $payment->reference_number = $paymentTransaction->PaymentInfo->TransactionID;
        $payment->description = 'PayPal Legacy Transaction';

        if ($payment->status === 'failed') {
            $payment->paid = false;
            $payment->captured = false;
            $payment->outcome = 'issuer_declined';
            $payment->failure_code = 'call_issuer';
            $payment->failure_message = 'The card has been declined for an unknown reason.';
        } else {
            $payment->paid = true;
            $payment->captured = true;
            $payment->captured_at = fromUtc($paymentTransaction->PaymentInfo->PaymentDate);
            $payment->outcome = 'authorized';
        }

        $payment->source_account_id = $account->id ?? null;
        $payment->gateway_type = 'paypalexpress';
        $payment->gateway_customer = $paymentTransaction->PayerInfo->PayerID;
        $payment->payment_audit_log = 'json:' . json_encode($paymentTransaction);
        $payment->created_at = fromUtc($paymentTransaction->PaymentInfo->PaymentDate);
        $payment->save();

        return $payment;
    }

    /**
     * Create a Refund from a Payment Transaction.
     *
     * @param \PayPal\EBLBaseComponents\PaymentTransactionType $paymentTransaction
     * @return \Ds\Models\Payment
     */
    private function createRefundFromPaymentTransaction(PaymentTransactionType $paymentTransaction): Payment
    {
        $payment = Payment::query()
            ->where('reference_number', $paymentTransaction->PaymentInfo->ParentTransactionID)
            ->firstOrFail();

        $refund = Refund::query()
            ->where('reference_number', $paymentTransaction->PaymentInfo->TransactionID)
            ->first();

        if ($refund) {
            return $payment;
        }

        $refund = new Refund;
        $refund->status = 'succeeded';
        $refund->reference_number = $paymentTransaction->PaymentInfo->TransactionID;
        $refund->amount = abs($paymentTransaction->PaymentInfo->GrossAmount->value);
        $refund->currency = $paymentTransaction->PaymentInfo->GrossAmount->currencyID;
        $refund->reason = 'requested_by_customer';
        $refund->refunded_by_id = 1;
        $refund->created_at = fromUtc($paymentTransaction->PaymentInfo->PaymentDate);
        $refund->refund_audit_log = 'json:' . json_encode($paymentTransaction);

        $payment->refunds()->save($refund);

        // apply refund to the first order/transaction linked to the payment
        $entity = $payment->orders()->first() ?? $payment->transactions()->first();

        if ($entity) {
            $entity->refunded_at = $refund->created_at;
            $entity->refunded_amt = $refund->amount;
            $entity->refunded_auth = $refund->reference_number;
            $entity->refunded_by = $refund->refunded_by_id;
            $entity->save();
        }

        return $payment;
    }
}
