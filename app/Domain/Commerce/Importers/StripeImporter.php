<?php

namespace Ds\Domain\Commerce\Importers;

use Ds\Domain\Commerce\AbstractImporter;
use Ds\Domain\Commerce\Gateways\StripeGateway;
use Ds\Domain\Commerce\Models\PaymentProvider;
use Ds\Domain\Shared\DateTime;
use Ds\Enums\RecurringPaymentProfileStatus;
use Ds\Models\AccountType;
use Ds\Models\Member as Account;
use Ds\Models\Order;
use Ds\Models\OrderItem;
use Ds\Models\Payment;
use Ds\Models\PaymentMethod;
use Ds\Models\RecurringPaymentProfile;
use Ds\Models\Refund;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Stripe\Card;
use Stripe\Charge;
use Stripe\Customer;
use Stripe\Invoice;
use Stripe\InvoiceLineItem;
use Stripe\Subscription;

class StripeImporter extends AbstractImporter
{
    /** @var \Ds\Domain\Commerce\Gateways\StripeGateway */
    protected $gateway;

    /**
     * Set the Payment Provider.
     *
     * @param \Ds\Domain\Commerce\Models\PaymentProvider $provider
     */
    protected function setProvider(PaymentProvider $provider)
    {
        parent::setProvider($provider);

        if (! $this->gateway || ! ($provider->gateway instanceof StripeGateway)) {
            throw new InvalidArgumentException('StripeImporter requires a StripeGateway');
        }
    }

    /**
     * Import a Payment from a gateway.
     *
     * @param mixed $reference
     * @return \Ds\Models\Payment|null
     */
    public function importPaymentFromGateway($reference): ?Payment
    {
        $customer = null;
        $invoice = null;
        $subscription = null;

        if (is_array($reference)) {
            $customer = Arr::get($reference, 1);
            $invoice = Arr::get($reference, 2);
            $subscription = Arr::get($reference, 3);
            $reference = Arr::get($reference, 0);
        } elseif (is_string($reference)) {
            $reference = $this->gateway->getCharge($reference);
        } elseif (! is_object($reference) || ! ($reference instanceof Charge)) {
            throw new InvalidArgumentException('StripeImporter expects a referenceNumber or Charge');
        }

        if ($reference) {
            return $this->importPaymentFromCharge($reference, $customer, $invoice, $subscription);
        }

        return null;
    }

    /**
     * Import a Payment from a Charge.
     *
     * @param \Stripe\Charge $charge
     * @return \Ds\Models\Payment|null
     */
    private function importPaymentFromCharge(
        Charge $charge,
        Customer $customer = null,
        Invoice $invoice = null,
        Subscription $subscription = null
    ): ?Payment {
        $payment = Payment::query()
            ->where('reference_number', $charge->id)
            ->first();

        if ($payment) {
            return $payment;
        }

        if (empty($charge->customer)) {
            $account = null;
        } else {
            if (empty($customer)) {
                $customer = $this->gateway->getCustomer($charge->customer);
            }

            $account = $this->findOrCreateAccountFromCustomer($customer);
        }

        if (empty($charge->invoice)) {
            $payment = $this->createPaymentFromCharge($charge, $account);

            $this->createOrderFromPayment($payment, 'ST');
        } else {
            if (empty($invoice)) {
                $invoice = $this->gateway->getInvoice($charge->invoice);
            }

            foreach ($invoice->lines->data as $line) {
                if ($line->type === 'subscription') {
                    $subscriptionId = $line->subscription ?? $line->id;

                    if (empty($subscription) || $subscription->id !== $subscriptionId) {
                        $subscription = $this->gateway->getSubscription($subscriptionId);
                    }

                    $rpp = $this->findRecurringPaymentProfileFromSubscription($subscription);
                    $payment = $this->createPaymentFromCharge($charge, $rpp->member ?? $account);

                    if ($rpp) {
                        $this->createTransactionFromPayment(
                            $payment,
                            $rpp,
                            money($line->amount / 100, $line->currency)
                        );

                        $this->updateRecurringPaymentProfileFromSubscription($subscription, $rpp);
                    } else {
                        $this->createOrderFromInvoiceLineItem($line, $subscription, $payment);
                    }

                    // There should never be multiple subs on a single invoice
                    // https://stripe.com/docs/billing/subscriptions/multiple
                    break;
                }
            }
        }

        return $payment;
    }

    /**
     * Find the Recurring Payment Profile for a given Subscription.
     *
     * @param \Stripe\Subscription $subscription
     * @return \Ds\Models\RecurringPaymentProfile|null
     */
    private function findRecurringPaymentProfileFromSubscription(Subscription $subscription): ?RecurringPaymentProfile
    {
        return RecurringPaymentProfile::query()
            ->where('is_manual', true)
            ->where('is_locked', true)
            ->where('stripe_subscription_id', $subscription->id)
            ->first();
    }

    /**
     * Update a Recurring Payment Profile with a given Subscription.
     *
     * @param \Stripe\Subscription $subscription
     * @param \Ds\Models\RecurringPaymentProfile $rpp
     * @return void
     */
    private function updateRecurringPaymentProfileFromSubscription(
        Subscription $subscription,
        RecurringPaymentProfile $rpp
    ): void {
        if (in_array($subscription->status, ['canceled', 'incomplete_expired', 'unpaid'])) {
            $rpp->status = RecurringPaymentProfileStatus::CANCELLED;
        } elseif ($subscription->status === 'past_due') {
            $rpp->status = RecurringPaymentProfileStatus::SUSPENDED;
        } else {
            $rpp->status = RecurringPaymentProfileStatus::ACTIVE;
        }

        $rpp->final_payment_due_date = fromUtc($subscription->ended_at);
        $rpp->billing_cycle_anchor = fromUtc($subscription->current_period_end);
        $rpp->next_billing_date = fromUtc($subscription->current_period_end);
        $rpp->save();
    }

    /**
     * Create an Order from an Invoice Line Item.
     *
     * @param \Stripe\InvoiceLineItem $line
     * @param \Stripe\Subscription $subscription
     * @param \Ds\Models\Payment $payment
     * @return \Ds\Models\Order
     */
    private function createOrderFromInvoiceLineItem(
        InvoiceLineItem $line,
        Subscription $subscription,
        Payment $payment
    ): Order {
        return $this->createOrderFromPayment(
            $payment,
            'ST',
            function (OrderItem $item, $save) use ($line, $subscription, $payment) {
                $intervals = [
                    'day' => 'daily',
                    'week' => 'weekly',
                    'month' => 'monthly',
                    'year' => 'annually',
                ];

                $item->price = $line->amount / 100;
                $item->recurring_amount = $item->price;
                $item->recurring_frequency = $intervals[$subscription->plan->interval] ?? 'monthly';
                $item->recurring_with_initial_charge = true;
                $item->recurring_day = fromUtc($payment->created_at)->day;
                $save();

                $this->createRecurringPaymentProfileFromSubscription(
                    $subscription,
                    $item,
                    $payment->account
                );
            }
        );
    }

    /**
     * Create a Recurring Payment Profile from a Subscription.
     *
     * @param \Stripe\Subscription $subscription
     * @param \Ds\Models\OrderItem $item
     * @param \Ds\Models\Account $account
     * @return \Ds\Models\RecurringPaymentProfile
     */
    private function createRecurringPaymentProfileFromSubscription(
        Subscription $subscription,
        OrderItem $item,
        Account $account
    ): RecurringPaymentProfile {
        $rpp = new RecurringPaymentProfile;
        $rpp->is_manual = true;
        $rpp->is_locked = true;
        $rpp->member_id = $account->id;
        $rpp->stripe_subscription_id = $subscription->id;
        $rpp->subscriber_name = $account->display_name;
        $rpp->profile_start_date = fromUtc($subscription->start_date ?? $subscription->start ?? $subscription->created);
        $rpp->profile_reference = $item->order->client_uuid;
        $rpp->description = $subscription->plan->name;
        $rpp->transaction_type = 'Donation';
        $rpp->billing_period = ucfirst($subscription->plan->interval);
        $rpp->amt = $subscription->plan->amount / 100;
        $rpp->currency_code = strtoupper($subscription->plan->currency);
        $rpp->init_amt = $item->total;
        $rpp->num_cycles_completed = 1;
        $rpp->last_payment_date = fromUtc($rpp->profile_start_date);
        $rpp->last_payment_amt = $rpp->init_amt;
        $rpp->aggregate_amount = $rpp->init_amt;
        $rpp->productorder_id = $item->order->id;
        $rpp->productorderitem_id = $item->id;
        $rpp->productinventory_id = $item->variant->id;
        $rpp->product_id = $item->variant->productid;

        $this->updateRecurringPaymentProfileFromSubscription($subscription, $rpp);

        return $rpp;
    }

    /**
     * Find or create an Account from a Stripe Customer.
     *
     * @param \Stripe\Customer $customer
     * @return \Ds\Models\Member
     */
    private function findOrCreateAccountFromCustomer(Customer $customer)
    {
        $account = Account::query()
            ->where('stripe_customer_id', $customer->id)
            ->orWhere(function ($query) use ($customer) {
                if (empty($customer->deleted)) {
                    $query->whereNull('stripe_customer_id');
                    $query->whereRaw('(email = ? or bill_email = ?)', [
                        $customer->email,
                        $customer->email,
                    ]);
                }
            })->first();

        if (isset($customer->deleted) && $customer->deleted) {
            throw new InvalidArgumentException("Stripe Customer ({$customer->id}) has been deleted.");
        }

        if (empty($account)) {
            $account = $this->createAccountFromCustomer($customer);
        }

        $account->stripe_customer_id = $customer->id;
        $account->save();

        foreach ($customer->sources->data ?? [] as $source) {
            if ($source instanceof Card) {
                $this->findOrCreatePaymentMethodFromCard($source, $account);
            }
        }

        return $account;
    }

    /**
     * Create an Account from a Stripe Customer.
     *
     * @param \Stripe\Customer $customer
     * @return \Ds\Models\Member
     */
    private function createAccountFromCustomer(Customer $customer): Account
    {
        // extract first and last name from a single name value
        $name = explode(' ', $customer->name ?? $customer->sources->data[0]->name ?? $customer->metadata->name ?? '');

        $lastName = array_pop($name) ?: null;
        $firstName = implode(' ', $name) ?: null;

        // discard state value if it's not a 2-character abbreviation
        $state = $customer->address->state ?? $customer->sources->data[0]->address_state ?? null;

        if (mb_strlen($state) > 2) {
            if ($state = $this->iso3166->subdivision($state, 'code')) {
                $state = Str::after($state, '-');
            }
        }

        // discard country value if it's not a 2-character abbreviation
        $country = $customer->address->country ?? $customer->sources->data[0]->address_country ?? $customer->sources->data[0]->country ?? null;

        if (mb_strlen($country) > 2) {
            $country = $this->iso3166->country($country, 'alpha_2');
        }

        $account = new Account;
        $account->first_name = $firstName;
        $account->last_name = $lastName;
        $account->bill_first_name = $account->first_name;
        $account->bill_last_name = $account->last_name;
        $account->bill_organization_name = null;
        $account->bill_email = $customer->email;
        $account->bill_address_01 = $customer->address->line1 ?? $customer->sources->data[0]->address_line1 ?? null;
        $account->bill_address_02 = $customer->address->line2 ?? $customer->sources->data[0]->address_line2 ?? null;
        $account->bill_city = $customer->address->city ?? $customer->sources->data[0]->address_city ?? null;
        $account->bill_state = $state;
        $account->bill_zip = $customer->address->postal_code ?? $customer->sources->data[0]->address_zip ?? null;
        $account->bill_country = $country;
        $account->bill_phone = $customer->phone ?? null;
        $account->is_active = true;
        $account->stripe_customer_id = $customer->id;
        $account->account_type_id = data_get(AccountType::default()->first(), 'id', 1);
        $account->created_at = fromUtc($customer->created);

        if (Account::where('email', $account->bill_email)->doesntExist()) {
            $account->email = $account->bill_email;
        }

        $account->setDisplayName();
        $account->save();

        return $account;
    }

    /**
     * Find or create a Payment Method from a Source.
     *
     * @param \Stripe\Card $card
     * @param \Ds\Models\Member $account
     * @return \Ds\Models\PaymentMethod|null
     */
    private function findOrCreatePaymentMethodFromCard(Card $card, Account $account): ?PaymentMethod
    {
        $paymentMethod = $account->paymentMethods()->where('token', $card->id)->first();

        if ($paymentMethod) {
            return $paymentMethod;
        }

        $expiry = str_pad($card->exp_month . substr($card->exp_year, 2, 2), 4, '0', STR_PAD_LEFT);
        $expiry = DateTime::createFromFormat('my-d', "$expiry-01")->endOfMonth();

        $paymentMethod = new PaymentMethod;
        $paymentMethod->member_id = $account->id;
        $paymentMethod->payment_provider_id = $this->provider->id;
        $paymentMethod->status = 'Active';
        $paymentMethod->token = $card->id;
        $paymentMethod->fingerprint = $card->fingerprint;
        $paymentMethod->display_name = $card->brand;
        $paymentMethod->account_type = $card->brand;
        $paymentMethod->account_last_four = $card->last4;
        $paymentMethod->cc_expiry = $expiry;
        $paymentMethod->billing_first_name = $account->bill_first_name;
        $paymentMethod->billing_last_name = $account->bill_last_name;
        $paymentMethod->billing_email = $account->bill_email;
        $paymentMethod->billing_address1 = $account->bill_address_01;
        $paymentMethod->billing_address2 = $account->bill_address_02;
        $paymentMethod->billing_city = $account->bill_city;
        $paymentMethod->billing_state = $account->bill_state;
        $paymentMethod->billing_postal = $account->bill_zip;
        $paymentMethod->billing_country = $account->bill_country;
        $paymentMethod->save();

        return $paymentMethod;
    }

    /**
     * Create a Payment from a Stripe Charge.
     *
     * @param \Stripe\Charge $charge
     * @return \Ds\Models\Payment
     */
    private function createPaymentFromCharge(Charge $charge, Account $account = null): Payment
    {
        $type = $charge->payment_method_details->type ?? null;

        if (! in_array($type, ['card', 'ach_debit'])) {
            throw new InvalidArgumentException("Charges of type [$type] can not be imported at this time.");
        }

        $payment = new Payment;
        $payment->livemode = $charge->livemode;
        $payment->type = ($type === 'ach_debit') ? 'bank' : 'card';
        $payment->status = $charge->status;
        $payment->amount = $charge->amount / 100;
        $payment->currency = $charge->currency;
        $payment->paid = $charge->paid;
        $payment->captured = $charge->captured;
        $payment->reference_number = $charge->id;
        $payment->description = 'Stripe Legacy Payment';
        $payment->dispute = $charge->dispute;
        $payment->failure_code = $charge->failure_code;
        $payment->failure_message = $charge->failure_message;
        $payment->outcome = $charge->outcome->type ?? 'invalid';
        $payment->source_account_id = $account->id ?? null;
        $payment->gateway_type = 'stripe';
        $payment->gateway_customer = $charge->source->customer ?? null;
        $payment->gateway_source = $charge->source->id ?? $charge->payment_method ?? null;
        $payment->card_funding = $charge->payment_method_details->card->funding ?? null;
        $payment->card_brand = $charge->payment_method_details->card->brand ?? null;
        $payment->card_fingerprint = $charge->payment_method_details->card->fingerprint ?? null;
        $payment->card_last4 = $charge->payment_method_details->card->last4 ?? null;
        $payment->card_exp_month = $charge->payment_method_details->card->exp_month ?? null;
        $payment->card_exp_year = $charge->payment_method_details->card->exp_year ?? null;
        $payment->card_cvc_check = $charge->payment_method_details->card->checks->cvc_check ?? null;
        $payment->card_tokenization_method = $charge->source->tokenization_method ?? null;
        $payment->card_entry_type = 'card_not_present';
        $payment->card_country = $charge->payment_method_details->card->country ?? null;
        $payment->card_name = $charge->source->name ?? null;
        $payment->card_address_line1 = $charge->source->address_line1 ?? null;
        $payment->card_address_line1_check = $charge->source->address_line1_check ?? null;
        $payment->card_address_line2 = $charge->source->address_line2 ?? null;
        $payment->card_address_city = $charge->source->address_city ?? null;
        $payment->card_address_state = $charge->source->address_state ?? null;
        $payment->card_address_zip = $charge->source->address_zip ?? null;
        $payment->card_address_zip_check = $charge->source->address_zip_check ?? null;
        $payment->card_address_country = $charge->source->address_country ?? null;
        $payment->bank_name = $charge->payment_method_details->ach_debit->bank_name ?? null;
        $payment->bank_fingerprint = $charge->payment_method_details->ach_debit->fingerprint ?? null;
        $payment->bank_last4 = $charge->payment_method_details->ach_debit->last4 ?? null;
        $payment->bank_account_holder_type = $charge->payment_method_details->ach_debit->account_holder_type ?? null;
        $payment->bank_routing_number = $charge->payment_method_details->ach_debit->routing_number ?? null;
        $payment->payment_audit_log = 'json:' . json_encode($charge);
        $payment->created_at = fromUtc($charge->created);

        if ($payment->type === 'bank' && $charge->status === 'pending') {
            $payment->paid = true;
        }

        if ($charge->paid) {
            $payment->captured_at = fromUtc($charge->created);
        }

        if ($account && $charge->source) {
            $paymentMethod = $account->paymentMethods()->where('token', $charge->source->id)->first();
            $payment->source_payment_method_id = $paymentMethod->id ?? null;
        }

        $payment->save();

        if (count($charge->refunds->data)) {
            foreach ($charge->refunds->data as $chargeRefund) {
                $refund = new Refund;
                $refund->status = $chargeRefund->status;
                $refund->reference_number = $chargeRefund->id;
                $refund->amount = $chargeRefund->amount / 100;
                $refund->currency = $chargeRefund->currency;
                $refund->reason = $chargeRefund->reason ?? 'requested_by_customer';
                $refund->refunded_by_id = 1;
                $refund->created_at = fromUtc($chargeRefund->created);
                $payment->refunds()->save($refund);
            }
        }

        return $payment;
    }
}
