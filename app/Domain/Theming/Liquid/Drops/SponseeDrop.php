<?php

namespace Ds\Domain\Theming\Liquid\Drops;

use Ds\Domain\Theming\Liquid\Drop;

class SponseeDrop extends Drop
{
    protected $attributes = [
        'id',
        'project',
        'reference_number',
        'full_name',
        'first_name',
        'last_name',
        'gender',
        'age',
        'phone',
        'last_timeline_update_at',
        'fields',
    ];

    protected function initialize($source)
    {
        $this->liquid = [
            'sponsored' => (bool) $source->is_sponsored,
            'url' => url($source->url),
            'biography' => do_shortcode($source->biography),
            'birth_date' => toUtcFormat($source->birth_date, 'Y-m-d'),
            'last_timeline_update_at' => toUtcFormat($source->last_timeline_update_on, 'json'),
            'longitude' => nullable_cast('double', $source->longitude ?: null),
            'latitude' => nullable_cast('double', $source->latitude ?: null),
            'thumbnail' => $source->image_thumbnail,
        ];
    }

    public function payment_options()
    {
        return [
            'one_time' => $this->source->payment_options_one_time,
            'recurring' => $this->source->payment_options_recurring,
        ];
    }

    public function fields()
    {
        return $this->source->segments;
    }

    public function timeline()
    {
        return $this->source->publicTimeline()->with('media')->get();
    }

    public function recurring_first_payment()
    {
        $type = $this->source->recurring_type ?? sys_get('rpp_default_type');

        if ($type === 'fixed') {
            return volt_setting('sponsorship_first_payment');
        }
    }
}
