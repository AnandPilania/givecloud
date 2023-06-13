<?php

namespace Ds\Http\Resources;

use Ds\Models\PromoCode;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \Ds\Models\Order */
class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->hashid,
            'is_recurring' => false,
            'contribution_number' => $this->client_uuid,
            'currency' => $this->currency_code,
            'customer_comments' => $this->comments,
            'notes' => $this->customer_notes,
            'is_paid' => $this->is_paid,
            'referral_source' => $this->referral_source,
            'ordered_at' => fromUtcFormat($this->ordered_at, 'api'),
            'updated_at' => fromUtcFormat($this->updated_at, 'api'),

            // Amounts
            'cover_costs_enabled' => (bool) $this->dcc_enabled_by_customer,
            'cover_costs_amount' => $this->dcc_total_amount,
            'discounts_amount' => $this->total_savings,
            'downloadable_item_count' => $this->download_items,
            'recurring_item_count' => $this->recurring_items,
            'shippable_item_count' => $this->shippable_items,
            'payment_type' => $this->payment_type,
            'shipping_amount' => $this->shipping_amount,
            'subtotal_amount' => $this->subtotal,
            'tax_amount' => $this->taxtotal,
            'total_amount' => $this->totalamount,
            'refunded_amount' => $this->refunded_amt,
            'refunded_at' => fromUtcFormat($this->refunded_at, 'api'),
            'balance_amount' => $this->balance_amt,

            // UTM & Tracking
            'http_referer' => $this->http_referer,
            'tracking_source' => $this->tracking_source,
            'tracking_medium' => $this->tracking_medium,
            'tracking_campaign' => $this->tracking_campaign,
            'tracking_term' => $this->tracking_term,
            'tracking_content' => $this->tracking_content,

            // Relationships
            'supporter' => $this->member ? new AccountResource($this->member) : null,
            'discounts' => $this->promoCodes->map(function (PromoCode $promoCode) {
                return new DiscountResource($promoCode);
            }),
            'line_items' => OrderItemResource::collection($this->items), // OrderItem[] (with downloadable)
            'payments' => PaymentResource::collection($this->payments),
            'tax_lines' => $this->tax_lines->map(function ($line) {
                return new TaxLineResource((object) $line);
            }),

            // Billing & Shipping
            'email' => $this->billingemail,
            'billing_address' => new BillingAddressResource($this),
            'shipping_method' => $this->shippable_items ? new OrderShippingMethodResource($this) : null,
            'shipping_address' => new ShippingAddressResource($this),
        ];
    }
}
