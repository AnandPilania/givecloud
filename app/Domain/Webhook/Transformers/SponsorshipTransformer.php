<?php

namespace Ds\Domain\Webhook\Transformers;

use Ds\Domain\Sponsorship\Models\Sponsorship;
use League\Fractal\TransformerAbstract;

class SponsorshipTransformer extends TransformerAbstract
{
    /**
     * @param \Ds\Domain\Sponsorship\Models\Sponsorship $sponsorship
     * @return array
     */
    public function transform(Sponsorship $sponsorship)
    {
        return [
            'id' => (int) $sponsorship->id,
            'project' => $sponsorship->project,
            'reference_number' => $sponsorship->reference_number,
            'sponsored' => (bool) $sponsorship->is_sponsored,
            'url' => url($sponsorship->url),
            'full_name' => $sponsorship->full_name,
            'first_name' => $sponsorship->first_name ?: null,
            'last_name' => $sponsorship->last_name ?: null,
            'biography' => $sponsorship->biography ?: null,
            'thumbnail' => $sponsorship->image_thumbnail ?: null,
            'gender' => $sponsorship->gender ?: null,
            'age' => $sponsorship->age ?: null,
            'birth_date' => toUtcFormat($sponsorship->birth_date, 'Y-m-d'),
            'school' => $sponsorship->school ?: null,
            'street_number' => $sponsorship->street_number ?: null,
            'street_name' => $sponsorship->street_name ?: null,
            'village' => $sponsorship->village ?: null,
            'region' => $sponsorship->region ?: null,
            'country' => $sponsorship->country ?: null,
            'phone' => $sponsorship->phone ?: null,
            'last_timeline_update_at' => toUtcFormat($sponsorship->last_timeline_update_on, 'json'),
            'longitude' => nullable_cast('double', $sponsorship->longitude ?: null),
            'latitude' => nullable_cast('double', $sponsorship->latitude ?: null),
            'fields' => $sponsorship->fields,
            'created_at' => toUtcFormat($sponsorship->created_at, 'json'),
            'updated_at' => toUtcFormat($sponsorship->updated_at, 'json'),
        ];
    }
}
