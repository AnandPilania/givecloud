<?php

namespace Ds\Jobs\Import;

use Ds\Domain\Commerce\Models\PaymentProvider;
use Ds\Domain\Commerce\Responses\TransactionResponse;
use Ds\Domain\Shared\Exceptions\MessageException;
use Ds\Domain\Sponsorship\Models\Sponsorship;
use Ds\Models\AccountType;
use Ds\Models\Member;
use Ds\Models\Order;
use Ds\Models\OrderItem;
use Ds\Models\PaymentMethod;
use Ds\Models\Product;
use Ds\Services\DonorPerfectService;
use Ds\Services\LedgerEntryService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Throwable;

class RecurringPaymentProfilesFromFile extends ImportJob
{
    /**
     * Version of the import process.
     */
    public function getColumnDefinitions(): Collection
    {
        /*
        'id' => 'order_number',
        [
            'validator' => 'nullable|max:45',
            'name' => 'Contribution Number',
            'hint' => 'The unique number associated with this contribution.',
            'sanitize' => null,
            'validator' => 'required|max:48|alpha_num|exists:productorder,invoicenumber',
            'messages' => [
                'order_number.exists' => 'Contribution (:value) already exists.'
            ],
            'custom_validator' = function ($row){
                return true;
            }
        ],
        */

        $headers = collect([]);

        $headers->push((object) [
            'id' => 'first_name',
            'name' => 'First Name',
            'validator' => 'nullable|max:45',
            'hint' => 'The supporter\'s first name.',
        ]);

        $headers->push((object) [
            'id' => 'last_name',
            'name' => 'Last Name',
            'validator' => 'nullable|max:45',
            'hint' => 'Recommended. The supporter\'s last name.',
        ]);

        $headers->push((object) [
            'id' => 'email',
            'name' => 'Email',
            'validator' => 'nullable|email|max:45',
            'hint' => 'Recommended. MUST BE UNIQUE. The supporter\'s email address. If this is not provided, this individual will not be able to login to your website. Supporters with duplicate emails will be merged.',
        ]);

        $headers->push((object) [
            'id' => 'donor_id',
            'name' => 'Donor ID',
            'validator' => 'nullable|numeric',
            'hint' => 'The ID of the Donor from DonorPerfect',
        ]);

        $headers->push((object) [
            'id' => 'pledge_id',
            'name' => 'Pledge ID',
            'validator' => 'nullable|numeric',
            'hint' => 'The Pledge ID of the Pledge from DonorPerfect',
        ]);

        $headers->push((object) [
            'id' => 'amount',
            'name' => 'Amount',
            'validator' => 'required|numeric',
            'hint' => 'The amount of the recurring payment.',
        ]);

        $headers->push((object) [
            'id' => 'frequency',
            'name' => 'Frequency',
            'validator' => 'required|in:W,BW,M,Q,BA,A',
            'hint' => 'Must be exactly one of W, BW, M, Q, BA, A (Weekly, Bi-Weekly, Monthly, Quarterly, Bi-Annually, Annually).',
        ]);

        $headers->push((object) [
            'id' => 'start_date',
            'name' => 'Start Date',
            'validator' => 'required|date',
            'hint' => 'The start date of the recurring payment. Must be formatted YYYY-MM-DD or MM/DD/YYYY. Try to be consistent with the format you choose.',
        ]);

        $headers->push((object) [
            'id' => 'next_bill_date',
            'name' => 'Next Billing Date',
            'validator' => 'required|date',
            'hint' => 'The next date this recurring profile should be charged. Must be formatted YYYY-MM-DD or MM/DD/YYYY. Try to be consistent with the format you choose.',
        ]);

        $headers->push((object) [
            'id' => 'vault_id',
            'name' => 'Vault ID',
            'validator' => 'nullable|max:32',
            'hint' => 'The NMI/SafeSave Customer Vault ID that represents the credit card or bank account on file.',
        ]);

        $headers->push((object) [
            'id' => 'stripe_customer_id',
            'name' => 'Stripe Customer ID',
            'validator' => 'nullable|max:32',
            'hint' => 'An existing Stripe Customer ID (e.x. cus_HstDZNyJ6QKDaI).',
        ]);

        $headers->push((object) [
            'id' => 'stripe_payment_method_id',
            'name' => 'Stripe Card/Bank Account/Payment Method ID',
            'validator' => 'nullable|max:32',
            'hint' => 'An existing Stripe Card/Bank Account/Payment Method ID (e.x. card_1D5PlvIXKcuEXw6cHQX9mUn6, ba_1GHmEJIPXcuQwX1cv6Hr7rIm or pm_1GHm4TIKXcuQwX1CgUsqoyld).',
        ]);

        $headers->push((object) [
            'id' => 'stripe_subscription_id',
            'name' => 'Stripe Subscription ID',
            'validator' => 'nullable|max:32',
            'hint' => 'An existing Stripe Subscription ID.',
        ]);

        $headers->push((object) [
            'id' => 'vanco_customer_ref',
            'name' => 'Vanco Customer Ref',
            'validator' => 'nullable|max:32',
            'hint' => 'An existing Vanco Customer Ref.',
        ]);

        $headers->push((object) [
            'id' => 'vanco_payment_method_ref',
            'name' => 'Vanco Payment Method Ref',
            'validator' => 'nullable|max:32',
            'hint' => 'An existing Vanco Payment Method Ref.',
        ]);

        $headers->push((object) [
            'id' => 'paypal_subscription_id',
            'name' => 'PayPal Subscription ID',
            'validator' => 'nullable|max:32',
            'hint' => 'An existing PayPal Subscription ID.',
        ]);

        $headers->push((object) [
            'id' => 'child_reference_number',
            'name' => 'Child Reference Number',
            'validator' => 'nullable|exists:sponsorship,reference_number',
            'hint' => 'Optional. Either Child Reference Number or Product Code MUST EXISTS. Identifies which sponsorship record in your Givecloud account this recurring payment relates to.',
        ]);

        $headers->push((object) [
            'id' => 'product_code',
            'name' => 'Product Code',
            'validator' => 'nullable|exists:product,code',
            'hint' => 'Optional. Either Child Reference Number or Product Code MUST EXISTS. Identifies which sponsorship record in your Givecloud account this recurring payment relates to.',
        ]);

        $headers->push((object) [
            'id' => 'general_ledger_code',
            'name' => 'Account Designation',
            'validator' => 'nullable|regex:/^[A-Z0-9](?!.*([_-])\1)(?!.*[_-]{2})([A-Z0-9_-]+)?[A-Z0-9]$/',
            'hint' => 'Optional. The general ledger (GL) code. May only contain uppercase letters, numbers, dashes and underscores. Must not contain any consecutive dashes or underscores and must not start or end with a dash or underscore.',
        ]);

        return $headers;
    }

    /**
     * Analyze a row.
     *
     * @param array $row
     */
    public function analyzeRow(array $row)
    {
        $messages = [];

        if (isset($row['child_reference_number'])) {
            $existing_sponsor = \Ds\Domain\Sponsorship\Models\Sponsor::whereNull('order_item_id')
                ->whereHas('sponsorship', function ($q) use ($row) {
                    $q->where('reference_number', $row['child_reference_number']);
                })->whereHas('member', function ($q) use ($row) {
                    $q->where('donor_id', $row['donor_id']);
                })->with('member', 'sponsorship')
                ->first();

            if (isset($existing_sponsor)) {
                $messages[] = 'An existing sponsor record will be used. (' . e($existing_sponsor->member->display_name) . ' sponsoring ' . $existing_sponsor->sponsorship->full_name . ')';
            }
        }

        // try finding a pledge
        if ($row['pledge_id']) {
            $pledge = app('dpo')->table('dpgift')
                ->select('gift_id')
                ->where('gift_id', '=', $row['pledge_id'])
                ->where('record_type', '=', 'P')
                ->first();

            if (! $pledge) {
                $messages[] = 'Pledge ID (' . $row['pledge_id'] . ') not found in DonorPerfect.';
            }
        }

        // try finding a member
        if ($row['donor_id']) {
            $member = Member::where('donor_id', $row['donor_id'])->first();

            if (! $member) {
                $donor = app('dpo')->table('dp')
                    ->select('donor_id')
                    ->where('donor_id', '=', $row['donor_id'])
                    ->first();

                if (! $donor) {
                    throw new MessageException('CANNOT IMPORT. No GC supporter found or Donor in DonorPerfect.');
                }

                $messages[] = 'Will import Donor ID ' . $donor->donor_id . ' from DonorPerfect.';
            }
        } elseif (empty($row['email'])) {
            throw new MessageException('CANNOT IMPORT. Either an Email or a Donor ID is required.');
        }

        // try getting the vault data
        if ($row['vault_id']) {
            try {
                /** @var PaymentProvider<\Ds\Domain\Commerce\Gateways\NMIGateway> */
                $provider = PaymentProvider::query()
                    ->where('enabled', true)
                    ->whereIn('provider', ['nmi', 'safesave'])
                    ->firstOrFail();

                $vault = $provider->getCustomerVault($row['vault_id']);

                if (empty($vault)) {
                    throw new ModelNotFoundException;
                }
            } catch (Throwable $e) {
                $messages[] = "NMI/SAFESAVE CUSTOMER VAULT [{$row['vault_id']}] NOT FOUND. This will be a manual payment.";
            }
        } elseif ($row['paypal_subscription_id']) {
            try {
                $provider = PaymentProvider::query()
                    ->where('enabled', true)
                    ->whereIn('provider', ['paypalexpress'])
                    ->firstOrFail();

                $subscription = $provider->getRecurringPaymentsProfile($row['paypal_subscription_id']);

                if (empty($subscription)) {
                    throw new ModelNotFoundException;
                }
            } catch (Throwable $e) {
                $messages[] = "PAYPAL SUBSCRIPTION [{$row['paypal_subscription_id']}] NOT FOUND. This will be a manual payment.";
            }
        } elseif ($row['stripe_customer_id'] && $row['stripe_payment_method_id']) {
            try {
                $provider = PaymentProvider::query()
                    ->where('enabled', true)
                    ->where('provider', 'stripe')
                    ->firstOrFail();

                if (Str::startsWith($row['stripe_payment_method_id'], 'pm_')) {
                    $provider->getCustomer($row['stripe_customer_id']);
                    $provider->getPaymentMethod($row['stripe_payment_method_id']);
                } else {
                    $provider->getCard($row['stripe_customer_id'], $row['stripe_payment_method_id']);
                }
            } catch (Throwable $e) {
                $messages[] = "STRIPE CUSTOMER [{$row['stripe_customer_id']}] OR PAYMENT METHOD [{$row['stripe_payment_method_id']}] NOT FOUND. This will be a manual payment.";
            }
        } elseif ($row['stripe_subscription_id']) {
            try {
                $provider = PaymentProvider::query()
                    ->where('enabled', true)
                    ->whereIn('provider', ['stripe'])
                    ->firstOrFail();

                $provider->getSubscription($row['stripe_subscription_id']);
            } catch (Throwable $e) {
                $messages[] = "STRIPE SUBSCRIPTION [{$row['stripe_subscription_id']}] NOT FOUND. This will be a manual payment.";
            }
        } elseif ($row['vanco_customer_ref'] && $row['vanco_payment_method_ref']) {
            try {
                $provider = PaymentProvider::query()
                    ->where('enabled', true)
                    ->where('provider', 'vanco')
                    ->firstOrFail();

                $provider->getPaymentMethod($row['vanco_customer_ref'], $row['vanco_payment_method_ref']);
            } catch (Throwable $e) {
                $messages[] = "VANCO CUSTOMER [{$row['vanco_customer_ref']}] OR PAYMENT METHOD [{$row['vanco_payment_method_ref']}] NOT FOUND. This will be a manual payment.";
            }
        } else {
            $messages[] = 'This will be a manual payment.';
        }

        return (count($messages)) ? implode(' ', $messages) : null;
    }

    /**
     * Import a row.
     *
     * @param array $row
     */
    public function importRow(array $row)
    {
        $txnResponse = null;

        if ($row['vault_id']) {
            $paymentProvider = PaymentProvider::query()
                ->where('enabled', true)
                ->whereIn('provider', ['nmi', 'safesave'])
                ->firstOrFail();
            $txnResponse = $this->getNetworkMerchantsTransactionResponse($paymentProvider, $row);
        } elseif ($row['paypal_subscription_id']) {
            $paymentProvider = PaymentProvider::query()
                ->where('enabled', true)
                ->whereIn('provider', ['paypalexpress'])
                ->firstOrFail();
        } elseif ($row['stripe_payment_method_id'] || $row['stripe_subscription_id']) {
            $paymentProvider = PaymentProvider::query()
                ->where('enabled', true)
                ->whereIn('provider', ['stripe'])
                ->firstOrFail();
            $txnResponse = $this->getStripeTransactionResponse($paymentProvider, $row);
        } elseif ($row['vanco_customer_ref']) {
            $paymentProvider = PaymentProvider::query()
                ->where('enabled', true)
                ->whereIn('provider', ['vanco'])
                ->firstOrFail();
            $txnResponse = $this->getVancoTransactionResponse($paymentProvider, $row);
        } else {
            $paymentProvider = PaymentProvider::query()
                ->where('enabled', true)
                ->whereIn('provider_type', ['offline'])
                ->firstOrFail();
        }

        // find the existing member
        $row['start_date'] = fromUtc($row['start_date']);
        $sponsorship = ($row['child_reference_number']) ? Sponsorship::where('reference_number', $row['child_reference_number'])->first() : null;
        $product = ($row['product_code']) ? Product::whereCode($row['product_code'])->with('variants')->first() : null;

        if ($row['donor_id']) {
            try {
                $member = Member::where('donor_id', $row['donor_id'])->firstOrFail();
            } catch (ModelNotFoundException $e) {
                $member = app(DonorPerfectService::class)->createAccountFromDonorId($row['donor_id']);
            }
        } elseif ($row['email']) {
            try {
                $member = Member::where('email', $row['email'])->firstOrFail();
            } catch (ModelNotFoundException $e) {
                $member = new Member;
                $member->first_name = $row['first_name'];
                $member->last_name = $row['last_name'];
                $member->email = $row['email'];
                $member->ship_first_name = $member->first_name;
                $member->ship_last_name = $member->last_name;
                $member->ship_email = $member->email;
                $member->bill_first_name = $member->first_name;
                $member->bill_last_name = $member->last_name;
                $member->bill_email = $member->email;
                $member->account_type_id = data_get(AccountType::default()->first(), 'id', 1);
                $member->save();
            }
        }

        // translate frequency (W, BW, M, Q, BA, A) (matches DP)
        switch ($row['frequency']) {
            case 'W':  $frequency = 'weekly'; break;
            case 'BW': $frequency = 'biweekly'; break;
            case 'Q':  $frequency = 'quarterly'; break;
            case 'BA': $frequency = 'biannually'; break;
            case 'A':  $frequency = 'annually'; break;
            default:   $frequency = 'monthly'; break;
        }

        if ($product) {
            $variant = $product->variants->firstWhere('billing_period', $frequency);

            if (empty($variant)) {
                $variant = $product->defaultVariant ?? $product->variants->first();
            }
        }

        // build the place-holder order
        $order = new Order;
        $order->payment_provider_id = $paymentProvider->id;
        $order->client_uuid = strtoupper(uuid());
        $order->client_ip = null;
        $order->client_browser = null;
        $order->started_at = $row['start_date'];
        $order->currency_code = sys_get('dpo_currency');
        $order->ordered_at = $row['start_date'];
        $order->source = 'Import';
        $order->is_pos = false;
        $order->dp_sync_order = ($row['pledge_id']) ? true : false;
        $order->alt_contact_id = ($row['pledge_id']) ? $row['donor_id'] : null;
        $order->alt_transaction_id = ($row['pledge_id']) ? $row['pledge_id'] : null;
        $order->currency_code = sys_get('dpo_currency');
        $order->tax_receipt_type = sys_get('tax_receipt_type');
        $order->save();

        // populate the order with the member's info
        $order->populateMember($member);

        // add the order item
        $item = new OrderItem;
        $item->productorderid = $order->id;
        $item->sponsorship_id = $sponsorship->id ?? null;
        $item->productinventoryid = $variant->id ?? null;
        $item->original_variant_id = $variant->id ?? null;
        $item->price = 0;
        $item->qty = 1;
        $item->recurring_frequency = $frequency;
        $item->recurring_day_of_week = null;
        $item->recurring_day = $row['start_date']->day;
        $item->recurring_amount = $row['amount'];
        $item->recurring_with_initial_charge = false;
        $item->general_ledger_code = $row['general_ledger_code'] ?? null;
        $item->alt_transaction_id = $row['pledge_id'] ?? null;

        if (in_array($frequency, ['weekly', 'biweekly'])) {
            $item->recurring_day_of_week = $row['start_date']->dayOfWeek;
            $item->recurring_day = null;
        }

        $item->save();

        $order->loadLoaded('items');
        $order->updateAggregates();

        // pay for the order
        $order->totalamount = 0;
        $order->confirmationdatetime = $row['start_date'];
        $order->createddatetime = $row['start_date'];
        $order->ordered_at = fromUtc($order->ordered_at ?? $order->createddatetime);
        $order->invoicenumber = $order->client_uuid;
        $order->is_processed = true;

        if (isset($txnResponse)) {
            $paymentMethod = PaymentMethod::query()
                ->where('member_id', $order->member_id)
                ->where('payment_provider_id', $order->payment_provider_id)
                ->where('token', $txnResponse->getSourceToken())
                ->active()
                ->first();

            if (empty($paymentMethod)) {
                $paymentMethod = new PaymentMethod;
                $paymentMethod->member_id = $order->member_id;
                $paymentMethod->payment_provider_id = $order->payment_provider_id;
                $paymentMethod->status = 'PENDING';
                $paymentMethod->billing_first_name = $order->billing_first_name;
                $paymentMethod->billing_last_name = $order->billing_last_name;
                $paymentMethod->billing_email = $order->billingemail;
                $paymentMethod->billing_address1 = $order->billingaddress1;
                $paymentMethod->billing_address2 = $order->billingaddress2;
                $paymentMethod->billing_city = $order->billingcity;
                $paymentMethod->billing_state = $order->billingstate;
                $paymentMethod->billing_postal = $order->billingzip;
                $paymentMethod->billing_country = $order->billingcountry;
                $paymentMethod->billing_phone = $order->billingphone;
                $paymentMethod->save();

                $paymentMethod->updateWithTransactionResponse($txnResponse);
            }

            $order->payment_method_id = $paymentMethod->id;
            $order->updateWithTransactionResponse($txnResponse);
        } else {
            $order->payment_other_reference = 'unknown';
            $order->payment_other_note = 'from import';
        }

        $order->iscomplete = 1;
        $order->save();

        // setup sponsorships (if necessary)
        if ($item->sponsorship_id) {
            $item->createSponsor('Import', true);
        }

        // setup the rpp
        foreach ($order->initializeRecurringPayments() as $rpp) {
            $rpp->profile_start_date = $row['start_date'];
            $rpp->billing_cycle_anchor = $row['start_date']->copy();

            if ($row['next_bill_date']) {
                $rpp->next_billing_date = fromUtc($row['next_bill_date']);
                $rpp->setBillingCycleAnchorForFirstPossibleStartDate('natural', null, null, $rpp->next_billing_date);
            }

            if ($row['paypal_subscription_id'] || $row['stripe_subscription_id']) {
                $rpp->is_manual = 1;
                $rpp->is_locked = 1;
            }

            if ($row['paypal_subscription_id']) {
                $rpp->paypal_subscription_id = $row['paypal_subscription_id'];
            }

            if ($row['stripe_subscription_id']) {
                $rpp->stripe_subscription_id = $row['stripe_subscription_id'];
            }

            $rpp->save();
        }

        app(LedgerEntryService::class)->make($order);

        return 'added_records';
    }

    private function getNetworkMerchantsTransactionResponse(PaymentProvider $paymentProvider, array $row): TransactionResponse
    {
        try {
            $vault = $paymentProvider->gateway->getCustomerVault($row['vault_id']);

            if (empty($vault)) {
                throw new MessageException("Customer Vault [{$row['vault_id']}] not found.");
            }
        } catch (Throwable $e) {
            throw new MessageException(sprintf('NMI Customer Vault [%s] error. %s', $row['vault_id'], $e->getMessage()));
        }

        return new TransactionResponse($paymentProvider, [
            'completed' => true,
            'response' => '1',
            'response_text' => 'APPROVED',
            'cc_number' => $vault['cc_number'] ?: null,
            'cc_exp' => $vault['cc_exp'] ?: null,
            'ach_account' => $vault['check_account'] ?: null,
            'ach_routing' => $vault['check_aba'] ?: null,
            'ach_type' => $vault['account_type'] ?: null,
            'ach_entity' => $vault['account_holder_type'] ?: null,
            'source_token' => $vault['customer_vault_id'] ?: null,
        ]);
    }

    private function getStripeTransactionResponse(PaymentProvider $paymentProvider, array $row): TransactionResponse
    {
        if ($row['stripe_subscription_id'] && empty($row['stripe_customer_id'])) {
            try {
                $subscription = $paymentProvider->getSubscription($row['stripe_subscription_id']);

                $row['stripe_customer_id'] = $subscription->customer;
            } catch (Throwable $e) {
                throw new MessageException(sprintf(
                    'Stripe Subscription [%s] error. %s',
                    $row['stripe_subscription_id'],
                    $e->getMessage()
                ));
            }
        }

        try {
            $customer = $paymentProvider->getCustomer($row['stripe_customer_id']);
        } catch (Throwable $e) {
            throw new MessageException(sprintf(
                'Stripe Customer [%s] error. %s',
                $row['stripe_customer_id'],
                $e->getMessage()
            ));
        }

        if ($row['stripe_subscription_id'] && empty($row['stripe_payment_method_id'])) {
            $row['stripe_payment_method_id'] = $customer->invoice_settings->default_payment_method ?? $customer->default_source;
        }

        if (empty($row['stripe_payment_method_id'])) {
            throw new MessageException('No Stripe Payment Method found.');
        }

        try {
            if (Str::startsWith($row['stripe_payment_method_id'], 'pm_')) {
                $data = $this->getStripePaymentMethodData($paymentProvider, $customer->id, $row['stripe_payment_method_id']);
            } else {
                $data = $this->getStripeCardData($paymentProvider, $customer->id, $row['stripe_payment_method_id']);
            }
        } catch (Throwable $e) {
            throw new MessageException(sprintf(
                'Stripe Payment Method [%s] error. %s',
                $row['stripe_payment_method_id'],
                $e->getMessage()
            ));
        }

        return new TransactionResponse($paymentProvider, array_merge($data, [
            'completed' => true,
            'response' => '1',
            'response_text' => 'APPROVED',
        ]));
    }

    private function getStripeCardData(PaymentProvider $paymentProvider, string $customerId, string $cardOrBankAccountId): array
    {
        $card = $paymentProvider->getCard($customerId, $cardOrBankAccountId);

        switch ($card->object) {
            case 'bank_account':
                return [
                    'account_type' => 'ACH',
                    'ach_account' => $card->last4,
                    'ach_routing' => $card->routing_number,
                    'ach_entity' => $card->account_holder_type,
                    'customer_ref' => $card->customer ?? $customerId,
                    'source_token' => $card->id,
                ];

            case 'card':
                return [
                    'account_type' => $card->brand,
                    'cc_number' => $card->last4,
                    'cc_exp' => str_pad($card->exp_month . substr($card->exp_year, 2, 2), 4, '0', STR_PAD_LEFT),
                    'customer_ref' => $card->customer ?? $customerId,
                    'source_token' => $card->id,
                ];

            default:
                return [
                    'customer_ref' => $card->customer ?? $customerId,
                    'source_token' => $card->id,
                ];
        }
    }

    private function getStripePaymentMethodData(PaymentProvider $paymentProvider, string $customerId, string $paymentMethodId): array
    {
        $paymentMethod = $paymentProvider->getPaymentMethod($paymentMethodId);

        switch ($paymentProvider->type) {
            case 'card':
                return [
                    'account_type' => $paymentMethod->card->brand,
                    'cc_number' => $paymentMethod->card->last4,
                    'cc_exp' => str_pad($paymentMethod->card->exp_month . substr($paymentMethod->card->exp_year, 2, 2), 4, '0', STR_PAD_LEFT),
                    'customer_ref' => $paymentMethod->customer ?? $customerId,
                    'source_token' => $paymentMethod->id,
                ];

            default:
                return [
                    'customer_ref' => $paymentMethod->customer ?? $customerId,
                    'source_token' => $paymentMethod->id,
                ];
        }
    }

    private function getVancoTransactionResponse(PaymentProvider $paymentProvider, array $row): TransactionResponse
    {
        try {
            $paymentMethod = $paymentProvider->getPaymentMethod($row['vanco_customer_ref'], $row['vanco_payment_method_ref']);
        } catch (Throwable $e) {
            throw new MessageException("VANCO CUSTOMER [{$row['vanco_customer_ref']}] OR PAYMENT METHOD [{$row['vanco_payment_method_ref']}] NOT FOUND.");
        }

        $data = [
            'completed' => true,
            'response' => '1',
            'response_text' => 'APPROVED',
            'customer_ref' => $paymentMethod['CustomerRef'] ?: null,
            'source_token' => $paymentMethod['PaymentMethodRef'] ?: null,
        ];

        if ($paymentMethod['AccountType'] === 'CC') {
            $data['account_type'] = $paymentMethod['CardType'];
            $data['cc_number'] = $paymentMethod['AccountNumber'];
            $data['cc_exp'] = str_pad($paymentMethod['CardExpMonth'] . substr($paymentMethod['CardExpYear'], 2, 2), 4, '0', STR_PAD_LEFT);
        } elseif ($paymentMethod['AccountType'] === 'C' || $paymentMethod['AccountType'] === 'S') {
            $data['ach_account'] = $paymentMethod['AccountNumber'] ?: null;
            $data['ach_routing'] = $paymentMethod['RoutingNumber'] ?: null;
            $data['ach_type'] = $paymentMethod['AccountType'] === 'C' ? 'checking' : 'savings';
            $data['ach_entity'] = null;
        }

        return new TransactionResponse($paymentProvider, $data);
    }
}
