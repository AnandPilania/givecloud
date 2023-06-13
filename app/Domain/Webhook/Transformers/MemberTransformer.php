<?php

namespace Ds\Domain\Webhook\Transformers;

use Ds\Models\Member;
use League\Fractal\TransformerAbstract;

class MemberTransformer extends TransformerAbstract
{
    /** @var array */
    protected $defaultIncludes = [
        'membership',
    ];

    /**
     * @param \Ds\Models\Member $member
     * @return array
     */
    public function transform(Member $member)
    {
        return [
            'id' => (int) $member->id,
            'active' => (bool) $member->is_active,
            'first_name' => $member->first_name ?: null,
            'last_name' => $member->last_name ?: null,
            'email' => $member->email ?: null,
            'type' => null,
            'email_opt_in' => (bool) $member->email_opt_in,
            'vendor_contact_id' => $member->donor_id ?: null,
            'billing_address' => [
                'first_name' => $member->bill_first_name ?: null,
                'last_name' => $member->bill_last_name ?: null,
                'company' => null,
                'email' => $member->bill_email ?: null,
                'address1' => $member->bill_address_01 ?: null,
                'address2' => $member->bill_address_02 ?: null,
                'city' => $member->bill_city ?: null,
                'state' => $member->bill_state ?: null,
                'zip' => $member->bill_zip ?: null,
                'country' => $member->bill_country ?: null,
                'phone' => $member->bill_phone ?: null,
            ],
            'shipping_address' => [
                'first_name' => $member->ship_first_name ?: null,
                'last_name' => $member->ship_last_name ?: null,
                'company' => null,
                'email' => $member->ship_email ?: null,
                'address1' => $member->ship_address_01 ?: null,
                'address2' => $member->ship_address_02 ?: null,
                'city' => $member->ship_city ?: null,
                'state' => $member->ship_state ?: null,
                'zip' => $member->ship_zip ?: null,
                'country' => $member->ship_country ?: null,
                'phone' => $member->ship_phone ?: null,
            ],
            'created_at' => toUtcFormat($member->created_at, 'json'),
            'updated_at' => toUtcFormat($member->updated_at, 'json'),
        ];
    }

    /**
     * @param \Ds\Models\Member $member
     * @return \League\Fractal\Resource\Item|void
     */
    public function includeMembership(Member $member)
    {
        if ($member->membership) {
            return $this->item($member->membership, new MembershipTransformer);
        }
    }
}
