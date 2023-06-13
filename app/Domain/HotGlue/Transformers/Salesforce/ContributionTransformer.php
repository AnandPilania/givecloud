<?php

namespace Ds\Domain\HotGlue\Transformers\Salesforce;

use Ds\Models\Order;
use Ds\Models\OrderItem;
use Illuminate\Support\Str;
use League\Fractal\TransformerAbstract;

class ContributionTransformer extends TransformerAbstract
{
    public function transform(Order $order): array
    {
        $contribution = [
            'external_id' => [
                'name' => sys_get('salesforce_opportunity_external_id'),
                'value' => $order->hashid,
            ],
            'type' => 'Contribution',
            'title' => 'Contribution #' . $order->invoicenumber ?: null,
            'close_date' => $order->ordered_at->toApiFormat(),
            'company_name' => optional($order->member)->bill_organization_name,
            'currency' => $order->currency_code,
            'deleted' => $order->deleted_at !== null,
            'expected_revenue' => $order->balance_amt * 100,
            'monetary_amount' => $order->balance_amt,
            'pipeline_stage_id' => 'Closed Won',
            'status' => 'Open',
            'win_probability' => 100,
            'description' => $this->description($order),
        ];

        if ($order->member && sys_get('salesforce_contact_external_id')) {
            $contribution['contact_external_id'] = [
                'name' => sys_get('salesforce_contact_external_id'),
                'value' => $order->member->hashid,
            ];
        }

        return $contribution;
    }

    protected function description(Order $order): string
    {
        $lines = collect([
            'fundraising_form' => data_get($order->fundraising_form, 'id'),
            'payment' => $order->payment_type_description,
            'is_recurring' => (int) $order->isrecurring,
            'recurring_description' => $order->recurring_description,
            'currency' => $order->currency_code,
            'customer_comments' => $order->comments,
            'notes' => $order->customer_notes,
            'is_paid' => (int) $order->is_paid,
            'referral_source' => $order->referral_source,
            'ordered_at' => fromUtcFormat($order->ordered_at, 'api'),
            'updated_at' => fromUtcFormat($order->updated_at, 'api'),

            // Amounts
            'cover_costs_enabled' => (bool) $order->dcc_enabled_by_customer,
            'cover_costs_amount' => $order->dcc_total_amount,
            'discounts_amount' => $order->total_savings,
            'downloadable_item_count' => $order->download_items,
            'recurring_item_count' => $order->recurring_items,
            'shippable_item_count' => $order->shippable_items,
            'payment_type' => $order->payment_type,
            'shipping_amount' => $order->shipping_amount,
            'subtotal_amount' => $order->subtotal,
            'tax_amount' => $order->taxtotal,
            'total_amount' => $order->totalamount,
            'refunded_amount' => $order->refunded_amt,
            'refunded_at' => fromUtcFormat($order->refunded_at, 'api'),
            'balance_amount' => $order->balance_amt,

            // UTM & Tracking
            'http_referer' => $order->http_referer,
            'tracking_source' => $order->tracking_source,
            'tracking_medium' => $order->tracking_medium,
            'tracking_campaign' => $order->tracking_campaign,
            'tracking_term' => $order->tracking_term,
            'tracking_content' => $order->tracking_content,
        ]);

        $order->items->each(function (OrderItem $item, $key) use ($lines) {
            $lines->offsetSet('item_' . ($key + 1), $item->long_description);
        });

        return collect($lines)->map(function ($value, $key) {
            $key = Str::headline($key);

            return $key . ': ' . $value;
        })->implode(PHP_EOL);
    }
}
