<?php

namespace Ds\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \Ds\Models\PromoCode */
class DiscountResource extends JsonResource
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
            'code' => $this->code,
            'description' => $this->description ?: null,
            'amount' => $this->discount,
            'free_shipping' => $this->is_free_shipping,
            'formatted' => $this->discount_formatted,
        ];
    }
}
