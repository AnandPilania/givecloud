<?php

namespace Ds\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\MissingValue;

/** @mixin \Ds\Models\Transaction */
class ContributionItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $orderItem = $this->recurringPaymentProfile->order_item;

        return [
            'id' => $this->hashid,
            'description' => $this->recurringPaymentProfile->description,
            'image_thumb' => $orderItem->image_thumb,
            'is_locked' => $orderItem->is_locked,
            'is_recurring' => true,
            'is_price_reduced' => $orderItem->is_price_reduced,
            'undiscounted_price' => $orderItem->undiscounted_price,
            'locked_original_price' => $orderItem->locked_original_price,
            'locked_variants_original_price' => $orderItem->locked_variants_original_price,
            'locked_variants_price' => $orderItem->locked_variants_price,
            'locked_variants_total' => $orderItem->locked_variants_total,
            'payment_string' => $this->payment_description,
            'public_url' => null,
            'recurring_description' => $this->recurringPaymentProfile->description,
            'reference' => null,
            'total' => $this->amt,

            'dpo_tribute_id' => null, // TODO
            'fields' => OrderItemFieldResource::collection($orderItem->fields),
            'gift_aid' => $orderItem->gift_aid,
            'is_tribute' => $orderItem->is_tribute,
            'price' => $this->amt,
            'qty' => 1,
            'recurring_profile_id' => $this->recurringPaymentProfile->profile_id,
            'recurring_amount' => $this->recurringPaymentProfile->amt,
            'recurring_day' => $this->recurringPaymentProfile->billing_period_day,
            'recurring_day_of_week' => optional($this->recurringPaymentProfile->billing_cycle_anchor)->format('l') ?? '',
            'recurring_with_dpo' => $orderItem->recurring_with_dpo,
            'recurring_with_initial_charge' => $orderItem->recurring_with_initial_charge,
            'recurring_cycles' => $orderItem->recurring_cycles,
            'recurring_starts_on' => fromUtcFormat($orderItem->recurring_starts_on, 'api'),
            'recurring_ends_on' => fromUtcFormat($orderItem->recurring_ends_on, 'api'),
            'sponsorship_is_expired' => $orderItem->sponsorship_is_expired,
            'total_tax_amt' => $this->tax_amt,
            'accounting' => ['code' => $orderItem->gl_code ?: null],

            'fundraiser' => FundraisingPageResource::make($orderItem->fundraisingPage),
            'variant' => VariantResource::make($this->variant()),
            'product' => ProductResource::make($this->product()),

            'tax_receipts' => TaxReceiptResource::collection($this->taxReceipts),
        ];
    }

    /** @return \Ds\Models\Product|\Illuminate\Http\Resources\MissingValue */
    protected function product()
    {
        if (! $this->recurringPaymentProfile->order_item->variant) {
            return new MissingValue;
        }

        $productOrdered = clone $this->recurringPaymentProfile->order_item->variant->product;

        return $productOrdered->unsetRelation('variants');
    }

    /** @return \Ds\Models\Variant|\Illuminate\Http\Resources\MissingValue  */
    protected function variant()
    {
        if (! $this->recurringPaymentProfile->order_item->variant) {
            return new MissingValue;
        }

        $variantOrdered = clone $this->recurringPaymentProfile->order_item->variant;

        return $variantOrdered->unsetRelation('product');
    }
}
