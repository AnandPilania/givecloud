<?php

namespace Ds\Domain\Theming\Liquid\Drops;

use Ds\Domain\Theming\Liquid\Drop;

class SponseeFieldDrop extends Drop
{
    protected $attributes = [
        'id',
        'name',
        'item',
        'value',
        'is_simple',
    ];

    public function options()
    {
        return $this->source->items;
    }
}
