<?php

namespace Ds\Domain\Theming\Liquid\Drops;

use Ds\Domain\Theming\Liquid\Drop;

class AccountTypeDrop extends Drop
{
    protected function initialize($source)
    {
        $this->liquid = [
            'id' => $source->id,
            'name' => $source->name,
            'is_organization' => $source->is_organization,
            'is_default' => $source->is_default,
            'on_web' => $source->on_web,
        ];
    }
}
