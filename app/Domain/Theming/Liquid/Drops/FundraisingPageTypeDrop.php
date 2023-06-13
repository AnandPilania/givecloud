<?php

namespace Ds\Domain\Theming\Liquid\Drops;

use Ds\Domain\Theming\Liquid\Drop;

class FundraisingPageTypeDrop extends Drop
{
    protected function initialize($source)
    {
        $this->liquid = [
            'id' => $source->id,
            'title' => $source->name,
            'summary' => $source->summary,
            'photo' => $source->photo,
        ];
    }
}
