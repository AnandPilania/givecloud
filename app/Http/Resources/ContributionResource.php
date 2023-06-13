<?php

namespace Ds\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \Ds\Models\Contribution
 */
class ContributionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        if ($this->resource->order) {
            return (new OrderResource($this->resource->order))->toArray($request);
        }

        return [
            'id' => $this->hashid,
            'currency' => $this->currency_code,
            'is_recurring' => true,
            'contribution_number' => $this->payment->reference_number ?? null,
            'customer_comments' => null,
            'notes' => null,
            'is_paid' => $this->payment->paid ?? false,
            'referral_source' => $this->referral_source,
            'ordered_at' => fromUtcFormat($this->contribution_date, 'api'),
            'updated_at' => fromUtcFormat($this->contribution_date, 'api'),

            // Amounts
            'cover_costs_enabled' => null, //(bool) $this->transaction->dcc_amount,
            'cover_costs_amount' => null, // $this->transaction->dcc_amount,
            'discounts_amount' => 0,
            'downloadable_item_count' => $this->downloadable_items,
            'recurring_item_count' => $this->recurring_items,
            'shippable_item_count' => $this->shippable_items,
            'payment_type' => $this->payment_type,
            'shipping_amount' => $this->transactions->sum('shipping_amt'),
            'subtotal_amount' => $this->transactions->sum('subtotal_amount'),
            'tax_amount' => $this->transactions->sum('tax_amt'),
            'total_amount' => $this->total,
            'refunded_amount' => $this->total_refunded,
            'refunded_at' => fromUtcFormat(optional($this->transactions->firstWhere('refunded_at'))->refunded_at, 'api'),
            'balance_amount' => $this->total - $this->total_refunded, //$this->transaction->balance_amount,

            // UTM & Tracking
            'http_referer' => null, //$this->http_referer,
            'tracking_source' => $this->tracking_source,
            'tracking_medium' => $this->tracking_medium,
            'tracking_campaign' => $this->tracking_campaign,
            'tracking_term' => $this->tracking_term,
            'tracking_content' => $this->tracking_content,

            // Relationships
            'supporter' => $this->supporter ? new AccountResource($this->supporter) : null,
            'discounts' => [] /*$this->promoCodes->map(function (PromoCode $promoCode) {
                return new DiscountResource($promoCode);
            })*/,
            'line_items' => ContributionItemResource::collection($this->transactions),
            'payments' => PaymentResource::collection([$this->payment]),
            'tax_lines' => $this->transactions->where('tax_amt')->map(function ($transaction) {
                return new TaxLineResource((object) [
                    'code' => $transaction->recurringPaymentProfile->order_item->taxes[0]->code ?? null,
                    'rate' => $transaction->recurringPaymentProfile->order_item->taxes[0]->rate ?? null,
                    'price' => $transaction->tax_amt,
                ]);
            }),

            // Billing & Shipping
            'email' => optional($this->supporter)->email,
            'billing_address' => new BillingAddressResource((object) [
                'billing_title' => $this->supporter->bill_title,
                'billing_display_name' => $this->supporter->display_name,
                'billing_first_name' => $this->supporter->bill_first_name,
                'billing_last_name' => $this->supporter->bill_last_name,
                'billingemail' => $this->supporter->bill_email,
                'billingaddress1' => $this->supporter->bill_address_01,
                'billingaddress2' => $this->supporter->bill_address_02,
                'billing_organization_name' => $this->supporter->bill_organization_name,
                'billingcity' => $this->supporter->bill_city,
                'billingstate' => $this->supporter->bill_state,
                'billingzip' => $this->supporter->bill_zip,
                'billingcountry' => $this->supporter->bill_country,
                'billingphone' => $this->supporter->bill_phone,
            ]),
            'shipping_method' => null,
            'shipping_address' => null,
        ];
    }
}
