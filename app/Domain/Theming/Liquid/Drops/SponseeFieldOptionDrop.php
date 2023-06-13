<?php

namespace Ds\Domain\Theming\Liquid\Drops;

use Ds\Domain\Theming\Liquid\Drop;

class SponseeFieldOptionDrop extends Drop
{
    protected $attributes = [
        'id',
        'name',
        'summary',
        'latitude',
        'longitude',
        'link',
        'target',
    ];
}
