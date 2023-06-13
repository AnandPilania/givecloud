<?php

namespace Ds\Services\Reports\PaymentsDetails;

use Ds\Enums\LedgerEntryType;
use Ds\Models\LedgerEntry;
use Ds\Models\OrderItem;
use Ds\Models\ProductCustomField;
use Ds\Models\Transaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;

class PaymentsDetailsService
{
    protected $filters = [
        Filters\AccountTypeFilter::class,
        Filters\BillingCountryFilter::class,
        Filters\CapturedAtFilter::class,
        Filters\CategoryFilter::class,
        Filters\FundraisingFormsFilter::class,
        Filters\GiftAidFilter::class,
        Filters\IpCountryFilter::class,
        Filters\ItemsFilter::class,
        Filters\LineItemTypeFilter::class,
        Filters\MembershipFilter::class,
        Filters\PaymentGatewayFilter::class,
        Filters\PaymentMethodFilter::class,
        Filters\RecurringFilter::class,
        Filters\SponsorshipFilter::class,
        Filters\SponsorshipCustomFieldsFilter::class,
        Filters\SearchFilter::class,
    ];

    /** @var array<string> */
    protected $customFieldNames;

    public function filteredQuery(): Builder
    {
        // Join on transactions and RPPs to search on Profile Name
        $profiles = DB::table('transactions')
            ->select('transactions.id as transaction_id', 'recurring_payment_profiles.profile_id')
            ->leftJoin('recurring_payment_profiles', function (JoinClause $join) {
                $join->on('recurring_payment_profiles.id', 'transactions.recurring_payment_profile_id');
            });

        return $this->applyFilters(
            LedgerEntry::query()
                ->leftJoin('productorder', 'ledger_entries.order_id', 'productorder.id')
                ->leftJoin('productorderitem', 'ledger_entries.item_id', 'productorderitem.id')
                ->leftJoin('productinventory', 'productorderitem.productinventoryid', 'productinventory.id')
                ->leftJoin('member', 'ledger_entries.supporter_id', 'member.id')
                ->leftJoinSub($profiles, 'profiles', function (JoinClause $join) {
                    $join->on('ledgerable_id', 'transaction_id')
                        ->where('ledgerable_type', Transaction::class);
                })->where(function ($query) {
                    $query->whereNull('productorder.is_spam');
                    $query->orWhere('productorder.is_spam', false);
                })->with([
                    'ledgerable.successfulPayments',
                    'item.taxes',
                    'item.variant.product',
                    'item.variant.membership',
                    'order.items.taxes',
                    'order.items.variant',
                    'order.shippingMethod',
                    'supporter.accountType',
                    'sponsorship',
                ])
        );
    }

    protected function applyFilters(Builder $query): Builder
    {
        collect($this->filters)->each(function ($filter) use ($query) {
            return (new $filter)($query);
        });

        return $query;
    }

    protected function getCustomFieldNames(): array
    {
        $this->customFieldNames ??= ProductCustomField::query()
            ->orderBy('productid')
            ->orderBy('sequence')
            ->whereIn(
                'productid',
                $this->filteredQuery()
                    ->select('productinventory.productid')
                    ->distinct()
                    ->pluck('productid')
            )->pluck('name')
            ->unique()
            ->values()
            ->all();

        return $this->customFieldNames;
    }

    public function buildExportableRow($row)
    {
        $reference = sprintf('Contribution #%s', $row->ledgerable->invoicenumber);

        if (is_a($row->ledgerable, Transaction::class)) {
            $reference = sprintf('Recurring Payment #%s', $row->ledgerable->recurringPaymentProfile->profile_id);
        }

        $description = $this->getLineItemDescription($row);

        if ($row->sponsorship) {
            $description = $row->sponsorship->display_name;
        }

        $payment = optional($row->ledgerable->successfulPayments)->first();

        $data = [
            toLocalFormat($row->captured_at, 'csv'), // Payment Date
            $reference,
            LedgerEntryType::labels()[$row->type],
            $description,
            $row->item->code ?? null,
            $row->gl_account,
            $row->qty ?: 1,
            money($row->amount)->format(),
            $row->item->variant->fair_market_value ?? null,
            money($payment->amount ?? 0)->format(),
            $payment->currency ?? $row->order->currency_code,
            $payment->source_type ?? '',
            ucfirst($payment->status ?? ''),
            ucfirst($payment->gateway_type ?? ''),
            ucfirst($payment->reference_number ?? ''),
            toLocalFormat($payment->captured_at ?? $row->ledgerable->captured_at, 'csv'),

            $row->order->referral_source,
            $row->order->ua_browser,
            $row->order->client_browser,
            $row->order->http_referer,

            $row->order->tracking_source,
            $row->order->tracking_medium,
            $row->order->tracking_campaign,
            $row->order->tracking_content,
            $row->order->tracking_term,

            optional($row->ledgerable->recurringPaymentProfile)->profile_id ?? '',
            $row->ledgerable->recurringPaymentProfile ? money($row->ledgerable->recurringPaymentProfile->aggregate_amount) : '',
            optional($row->ledgerable->recurringPaymentProfile)->billing_period ?? '',
            $row->ledgerable->recurringPaymentProfile
                ? toLocalFormat($row->ledgerable->recurringPaymentProfile->next_billing_date, 'csv')
                : '',
            $row->order->invoice_number,
            toLocalFormat($row->order->createdatetime, 'csv'),
            $row->order->source,
            $row->order->comments,

            trim($row->order->billing_title_name),
            trim($row->order->billing_first_name),
            trim($row->order->billing_last_name),
            $row->order->billingemail,
            $row->order->billingphone,
            $row->order->billingaddress1,
            $row->order->billingaddress2,
            $row->order->billingcity,
            $row->order->billingstate,
            $row->order->billingzip,
            $row->order->billingcountry,

            $row->supporter->accountType->name ?? null,
            $row->supporter->display_name ?? null,
            $row->supporter->dpo_id ?? null,

            $row->ledgerable->dpo_gift_id,
        ];

        foreach ($this->getCustomFieldNames() as $customFieldName) {
            $data[] = isset($row->item->fields)
                ? $row->item->fields->where('name', $customFieldName)->first()->value ?? null
                : null;
        }

        return $data;
    }

    public function getHeadersForExport(): array
    {
        $headers = [
            'Payment Date',
            'Reference',
            'Line Item Type',
            'Line Item Description',
            'SKU/Child reference',
            'GL Account',
            'Line Item Qty',
            'Line Item Amount',
            'Line Item Calculated FMV',
            'Payment Amount',
            'Currency',
            'Payment Method',
            'Payment Status',
            'Payment Gateway',
            'Payment Reference Number',
            'Payment Captured Date',

            'Referral Source',
            'Browser',
            'User agent',
            'Http Referrer',
            'Tracking info (UTM\'s) - Source',
            'Tracking info (UTM\'s) - Medium',
            'Tracking info (UTM\'s) - Campaign',
            'Tracking info (UTM\'s) - Content',
            'Tracking info (UTM\'s) - Term',

            'Recurring Payment Profile ID',
            'Recurring Amount',
            'Recurring Period',
            'Next Bill Date',
            'Original Order Number',
            'Original Ordered At',
            'Source',
            'Order Item Special Notes',

            'Supporter Billing Title',
            'Supporter Billing First Name',
            'Supporter Billing Last Name',
            'Supporter Billing Email',
            'Supporter Billing Phone',
            'Supporter Billing Address',
            'Supporter Billing Address 2',
            'Supporter Billing City',
            'Supporter Billing Province/State',
            'Supporter Billing Postal/ZIP',
            'Supporter Billing Country',
            'Supporter Type',
            'Supporter Organization name',
            'DP Donor ID',
            'DP Gift ID',
        ];

        return array_merge($headers, $this->getCustomFieldNames());
    }

    public function getLineItemDescription($row): string
    {
        if ($row->sponsorship) {
            return sprintf('Sponsorship for <a href="%s">%s</a>', route('backend.sponsorship.view', ['id' => $row->sponsorship_id]), $row->sponsorship->display_name);
        }

        if ($membership = data_get($row, 'item.variant.membership')) {
            return sprintf('Membership - <a href="%s">%s</a>', route('backend.memberships.edit', ['i' => $membership->getKey()]), $membership->name);
        }

        // Item Name
        if (in_array($row->type, [LedgerEntryType::LINE_ITEM, LedgerEntryType::DCC]) && $row->item->description) {
            return $row->item->description;
        }

        // Tax name
        if ($row->type === LedgerEntryType::TAX) {
            return $row->order->items->map(function (OrderItem $item) {
                return $item->taxes->implode('description');
            })->implode(', ');
        }

        // Shipping Method Name
        if ($row->type === LedgerEntryType::SHIPPING) {
            return $row->order->shipping_method_name;
        }

        return 'N/D';
    }

    public function getRowsForExport(): LazyCollection
    {
        return $this->filteredQuery()
            ->select('ledger_entries.*')
            ->lazy(500)
            ->map(function ($row) {
                return $this->buildExportableRow($row);
            });
    }
}
