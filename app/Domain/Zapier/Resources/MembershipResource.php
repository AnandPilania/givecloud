<?php

namespace Ds\Domain\Zapier\Resources;

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
            'sequence' => $this->sequence,
            'name' => $this->name,
            'description' => $this->description,
            'default_promo_code' => $this->default_promo_code,
            'default_url' => $this->default_url,
            'renewal_url' => $this->renewal_url,
            'days_to_expire' => $this->days_to_expire,
            'show_in_profile' => $this->show_in_profile,
            'public_name' => $this->public_name,
            'public_description' => $this->public_description,
            'members_can_manage_optin' => $this->members_can_manage_optin,
            'members_can_manage_optout' => $this->members_can_manage_optout,
            'members_can_view_directory' => $this->members_can_view_directory,
            'double_optin_required' => $this->double_optin_required,
            'dp_id' => $this->dp_id,
            'start_date' => $this->whenPivotLoaded('group_account', function () {
                return $this->pivot->start_date;
            }),
            'end_date' => $this->whenPivotLoaded('group_account', function () {
                return $this->pivot->end_date;
            }),
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_by' => $this->updated_by,
            'updated_at' => $this->updated_at,
            'deleted_by' => $this->deleted_by,
            'deleted_at' => $this->deleted_at,
            'starts_at' => $this->starts_at,
            'should_display_badge' => $this->should_display_badge,
        ];
    }
}
