<?php

namespace Ds\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \Ds\Models\Transaction */
class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->prefixed_id,
            'contribution_number' => $this->transaction_id,
            'currency' => $this->currency_code,
            'customer_comments' => null,
            'notes' => null,
            'is_paid' => $this->is_payment_accepted,
            'referral_source' => $this->recurringPaymentProfile->order->referral_source,
            'ordered_at' => fromUtcFormat($this->order_time, 'api'),
            'updated_at' => fromUtcFormat($this->order_time, 'api'),

            // Amounts
            'cover_costs_enabled' => (bool) $this->recurringPaymentProfile->order->dcc_enabled_by_customer,
            'cover_costs_amount' => $this->dcc_amount,
            'discounts_amount' => null,
            'downloadable_item_count' => $this->recurringPaymentProfile->order->download_items,
            'recurring_item_count' => $this->recurringPaymentProfile->order->recurring_items,
            'shippable_item_count' => $this->recurringPaymentProfile->order->shippable_items,
            'payment_type' => $this->payment_method_type,
            'shipping_amount' => $this->shipping_amt,
            'subtotal_amount' => $this->subtotal_amount,
            'tax_amount' => $this->tax_amt,
            'total_amount' => $this->amt,
            'refunded_amount' => $this->refunded_amt,
            'refunded_at' => fromUtcFormat($this->refunded_at, 'api'),
            'balance_amount' => $this->balance_amount,

            // Relationships
            'supporter' => $this->recurringPaymentProfile->member ? new AccountResource($this->recurringPaymentProfile->member) : null,
            'discounts' => [],
            'line_items' => OrderItemResource::collection([$this->recurringPaymentProfile->order_item]), // OrderItem[] (with downloadable)
            'payments' => PaymentResource::collection($this->payments),
            'tax_lines' => $this->recurringPaymentProfile->order->tax_lines->map(function ($line) {
                return new TaxLineResource((object) $line);
            }),

            // Billing & Shipping
            'email' => $this->recurringPaymentProfile->order->billingemail,
            'billing_address' => new BillingAddressResource($this->recurringPaymentProfile->order),
            'shipping_method' => $this->recurringPaymentProfile->order->shippable_items ? new OrderShippingMethodResource($this->recurringPaymentProfile->order) : null,
            'shipping_address' => new ShippingAddressResource($this->recurringPaymentProfile->order),
        ];
    }
}
