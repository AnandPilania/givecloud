<?php

namespace Ds\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \Ds\Models\Membership */
class GroupAccountTimespanResource extends JsonResource
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
            'id' => $this->pivot->hashid,
            'group_id' => $this->hashid,
            'name' => $this->name,
            'start_date' => toUtcFormat($this->pivot->start_date, 'api'),
            'end_date' => toUtcFormat($this->pivot->end_date, 'api'),
            'is_active' => $this->pivot->is_active,
            'is_expired' => $this->pivot->is_expired,
            'days_left' => $this->pivot->days_left,
            'related' => GroupAccountResource::collection($this->pivot->groupAccounts),
        ];
    }
}
