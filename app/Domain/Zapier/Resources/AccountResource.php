<?php

namespace Ds\Domain\Zapier\Resources;

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
            'display_name' => $this->display_name,
            'title' => $this->title,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'access' => $this->member,
            'addresses' => [
                'billing' => [
                    'title' => $this->bill_title,
                    'first_name' => $this->bill_first_name,
                    'last_name' => $this->bill_last_name,
                    'organization_name' => $this->bill_organization_name,
                    'email' => $this->bill_email,
                    'address_01' => $this->bill_address_01,
                    'address_02' => $this->bill_address_02,
                    'city' => $this->bill_city,
                    'state' => $this->bill_state,
                    'zip' => $this->bill_zip,
                    'country' => $this->bill_country,
                    'display_address' => $this->display_bill_address,
                    'phone' => $this->bill_phone,
                    'display_phone' => $this->display_bill_phone,
                ],
                'shipping' => [
                    'title' => $this->ship_title,
                    'first_name' => $this->ship_first_name,
                    'last_name' => $this->ship_last_name,
                    'organization_name' => $this->ship_organization_name,
                    'email' => $this->ship_email,
                    'address_01' => $this->ship_address_01,
                    'address_02' => $this->ship_address_02,
                    'city' => $this->ship_city,
                    'state' => $this->ship_state,
                    'zip' => $this->ship_zip,
                    'country' => $this->ship_country,
                    'phone' => $this->ship_phone,
                ],
            ],
            'sms_verified' => $this->sms_verified,
            'email_opt_in' => $this->email_opt_in,
            'is_active' => $this->is_active,
            'account_type' => $this->accountType->name,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_by' => $this->createdBy->display_name ?? '',
            'sign_up_method' => $this->sign_up_method,
            'referral_source' => $this->referral_source,
            'referral_code' => $this->referral_code,
            'nps' => $this->nps,
            'lifetime_donation_amount' => $this->lifetime_donation_amount,
            'lifetime_donation_count' => $this->lifetime_donation_count,
            'lifetime_purchase_amount' => $this->lifetime_purchase_amount,
            'lifetime_purchase_count' => $this->lifetime_purchase_count,
            'lifetime_fundraising_amount' => $this->lifetime_fundraising_amount,
            'lifetime_fundraising_count' => $this->lifetime_fundraising_count,
            'is_membership_expired' => $this->is_membership_expired,
            'membership' => $this->membership,
            'membership_expires_on' => $this->membership_expires_on,
            'groups' => $this->groups->map(function ($group) {
                return new MembershipResource($group);
            })->toArray(),
        ];
    }
}
