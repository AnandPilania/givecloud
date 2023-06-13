<?php

namespace Ds\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \Ds\Models\Member */
class AccountResource extends JsonResource
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
            'id_deprecated' => $this->id,
            'active' => (bool) $this->is_active,
            'display_name' => $this->display_name,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'type' => $this->accountType->name ?? null,
            'is_organization' => $this->accountType->is_organization ?? false,
            'email_opt_in' => (bool) $this->email_opt_in,
            'vendor_contact_id' => $this->donor_id,
            'billing_address' => [
                'first_name' => $this->bill_first_name,
                'last_name' => $this->bill_last_name,
                'company' => null,
                'email' => $this->bill_email,
                'address1' => $this->bill_address_01,
                'address2' => $this->bill_address_02,
                'city' => $this->bill_city,
                'state' => $this->bill_state,
                'zip' => $this->bill_zip,
                'country' => $this->bill_country,
                'phone' => $this->bill_phone,
            ],
            'shipping_address' => [
                'first_name' => $this->ship_first_name,
                'last_name' => $this->ship_last_name,
                'company' => null,
                'email' => $this->ship_email,
                'address1' => $this->ship_address_01,
                'address2' => $this->ship_address_02,
                'city' => $this->ship_city,
                'state' => $this->ship_state,
                'zip' => $this->ship_zip,
                'country' => $this->ship_country,
                'phone' => $this->ship_phone,
            ],
            'created_at' => toUtcFormat($this->created_at, 'api'),
            'updated_at' => toUtcFormat($this->updated_at, 'api'),
            'groups' => GroupAccountTimespanResource::collection($this->groupAccountTimespans),
        ];
    }
}
