<?php

namespace Ds\Domain\Webhook\Transformers;

use Ds\Models\Membership;
use League\Fractal\TransformerAbstract;

class MembershipTransformer extends TransformerAbstract
{
    /** @var array */
    protected $defaultIncludes = [
        'promos',
    ];

    /**
     * @param \Ds\Models\Membership $membership
     * @return array
     */
    public function transform(Membership $membership)
    {
        return [
            'id' => (int) $membership->id,
            'sequence' => (int) $membership->sequence ?: null,
            'name' => $membership->name ?: null,
            'description' => $membership->description ?: null,
            'welcome_url' => $membership->default_url ?: null,
            'start_date' => toUtcFormat($membership->start_at, 'Y-m-d'),
            'duration' => (int) $membership->days_to_expire ?: null,
            'vendor_membership_id' => $membership->dp_id ?: null,
            'created_at' => toUtcFormat($membership->created_at, 'json'),
            'updated_at' => toUtcFormat($membership->updated_at, 'json'),
        ];
    }

    /**
     * @param \Ds\Models\Membership $membership
     * @return \League\Fractal\Resource\Collection
     */
    public function includePromos(Membership $membership)
    {
        return $this->collection($membership->promoCodes, new PromoCodeTransformer);
    }
}
