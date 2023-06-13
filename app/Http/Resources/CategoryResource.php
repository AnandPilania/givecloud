<?php

namespace Ds\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \Ds\Models\Category */
class CategoryResource extends JsonResource
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
            'id' => $this->hash_id,
            'parent_id' => $this->parentCategory->hashid ?? null,
            'name' => $this->name,
            'handle' => $this->handle,
            'created_at' => fromUtcFormat($this->created_at, 'api'),
            'updated_at' => fromUtcFormat($this->updated_at, 'api'),
        ];
    }
}
