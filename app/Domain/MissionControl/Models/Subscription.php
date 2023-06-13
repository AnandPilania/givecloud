<?php

namespace Ds\Domain\MissionControl\Models;

use Ds\Common\DataAccess;

class Subscription extends DataAccess
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'trial_ends_on' => 'date',
        'purchased_date' => 'datetime',
    ];
}
