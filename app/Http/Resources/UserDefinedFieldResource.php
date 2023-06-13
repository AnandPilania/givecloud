<?php

namespace Ds\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \Ds\Models\UserDefinedField */
class UserDefinedFieldResource extends JsonResource
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
            'id' => $this->getKey(),
            'name' => $this->name,
            'entity' => $this->entity,
            'field_attributes' => $this->field_attributes,
            'field_type' => $this->field_type,
        ];
    }
}
