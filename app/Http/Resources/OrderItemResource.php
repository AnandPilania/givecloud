<?php

namespace Ds\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\MissingValue;

/** @mixin \Ds\Models\OrderItem */
class OrderItemResource extends JsonResource
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
            'description' => $this->description,
            'image_thumb' => $this->image_thumb,
            'is_locked' => $this->is_locked,
            'is_recurring' => $this->is_recurring,
            'is_price_reduced' => $this->is_price_reduced,
            'undiscounted_price' => $this->undiscounted_price,
            'locked_original_price' => $this->locked_original_price,
            'locked_variants_original_price' => $this->locked_variants_original_price,
            'locked_variants_price' => $this->locked_variants_price,
            'locked_variants_total' => $this->locked_variants_total,
            'payment_string' => $this->payment_string,
            'public_url' => $this->public_url,
            'recurring_description' => $this->recurring_description,
            'reference' => $this->reference,
            'total' => $this->total,

            'dpo_tribute_id' => $this->dpo_tribute_id,
            'fields' => OrderItemFieldResource::collection($this->fields),
            'gift_aid' => $this->gift_aid,
            'is_tribute' => $this->is_tribute,
            'price' => $this->price,
            'qty' => $this->qty,
            'recurring_profile_id' => optional($this->recurringPaymentProfile)->profile_id,
            'recurring_amount' => $this->recurring_amount,
            'recurring_day' => $this->recurring_day,
            'recurring_day_of_week' => $this->recurring_day_of_week,
            'recurring_with_dpo' => $this->recurring_with_dpo,
            'recurring_with_initial_charge' => $this->recurring_with_initial_charge,
            'recurring_cycles' => $this->recurring_cycles,
            'recurring_starts_on' => fromUtcFormat($this->recurring_starts_on, 'api'),
            'recurring_ends_on' => fromUtcFormat($this->recurring_ends_on, 'api'),
            'sponsorship_is_expired' => $this->sponsorship_is_expired,
            'total_tax_amt' => $this->total_tax_amt,
            'accounting' => ['code' => $this->gl_code ?: null],

            'fundraiser' => FundraisingPageResource::make($this->fundraisingPage),
            'variant' => VariantResource::make($this->variant()),
            'product' => ProductResource::make($this->product()),

            'tax_receipts' => $this->when($this->is_receiptable, TaxReceiptResource::collection($this->order->taxReceipts)),
        ];
    }

    /** @return \Ds\Models\Product|\Illuminate\Http\Resources\MissingValue */
    protected function product()
    {
        if (! $this->variant) {
            return new MissingValue;
        }

        $productOrdered = clone $this->variant->product;

        return $productOrdered->unsetRelation('variants');
    }

    /** @return \Ds\Models\Variant|\Illuminate\Http\Resources\MissingValue  */
    protected function variant()
    {
        if (! $this->variant) {
            return new MissingValue;
        }

        $variantOrdered = clone $this->variant;

        return $variantOrdered->unsetRelation('product');
    }
}
