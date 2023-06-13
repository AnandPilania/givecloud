<?php

namespace Ds\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \Ds\Models\FundraisingPage */
class FundraisingPageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->hashid,
            'title' => $this->title,
            'url' => $this->absolute_url,
            'category' => $this->category,
            'goal_deadline' => $this->goal_deadline,
            'supporter' => AccountResource::make($this->memberOrganizer),
        ];
    }
}
