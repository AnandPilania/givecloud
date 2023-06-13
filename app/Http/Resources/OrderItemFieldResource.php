<?php

namespace Ds\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \Ds\Models\ProductCustomField */
class OrderItemFieldResource extends JsonResource
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
            'field_id' => $this->hash_id,
            'name' => $this->name,
            'value' => $this->value_formatted,
        ];
    }
}
