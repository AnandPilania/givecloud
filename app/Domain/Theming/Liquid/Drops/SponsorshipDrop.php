<?php

namespace Ds\Domain\Theming\Liquid\Drops;

use Ds\Domain\Theming\Liquid\Drop;

class SponsorshipDrop extends Drop
{
    protected $attributes = [
        'id',
        'started_at',
        'ended_at',
        'lifetime_amt',
        'last_payment_at',
    ];

    public function sponsee()
    {
        return $this->source->sponsorship;
    }

    public function subscription()
    {
        return $this->source->recurringPaymentProfile;
    }
}
