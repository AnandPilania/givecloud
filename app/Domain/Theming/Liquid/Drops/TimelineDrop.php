<?php

namespace Ds\Domain\Theming\Liquid\Drops;

use Ds\Domain\Theming\Liquid\Drop;

class TimelineDrop extends Drop
{
    /** @var array */
    protected $attributes = [
        'id',
        'headline',
        'posted_on',
        'is_private',
        'message',
        'media',
        'tag',
        'icon_class',
        'created_at',
    ];
}
