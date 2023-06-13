<?php

namespace Ds\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \Ds\Models\Membership */
class MembershipResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'sequence' => $this->sequence,
            'default_url' => $this->default_url,
            'days_to_expire' => $this->days_to_expire,
            'show_in_profile' => $this->show_in_profile,
            'public_name' => $this->public_name,
            'public_description' => $this->public_description,
            'members_can_manage_optin' => (bool) $this->members_can_manage_optin,
            'members_can_manage_optout' => (bool) $this->members_can_manage_optout,
            'members_can_view_directory' => (bool) $this->members_can_view_directory,
            'double_optin_required' => (bool) $this->double_optin_required,
            'should_display_badge' => (bool) $this->should_display_badge,
            'dp_id' => $this->dp_id ? (int) $this->dp_id : null,
            'created_by' => $this->created_by,
            'created_at' => toUtcFormat($this->created_at, 'api'),
            'updated_by' => $this->updated_by,
            'updated_at' => toUtcFormat($this->updated_at, 'api'),
            'deleted_at' => toUtcFormat($this->deleted_at, 'api'),
            'deleted_by' => $this->deleted_by,
            'starts_at' => toUtcFormat($this->starts_at, 'api'),
        ];
    }
}
