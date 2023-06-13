<?php

namespace Ds\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GroupAccountResource extends JsonResource
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
            'name' => $this->group->name,
            'start_date' => toUtcFormat($this->start_date, 'api'),
            'end_date' => toUtcFormat($this->end_date, 'api'),
            'is_active' => $this->is_active,
            'is_expired' => $this->is_expired,
            'source' => $this->source,
            'end_reason' => $this->end_reason,
            'days_left' => $this->days_left,
        ];
    }
}
