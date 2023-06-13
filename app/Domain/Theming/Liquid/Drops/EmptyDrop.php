<?php

namespace Ds\Domain\Theming\Liquid\Drops;

use Ds\Domain\Theming\Liquid\Drop;

class EmptyDrop extends Drop
{
    protected function initialize($source)
    {
        $this->liquid = [
            'empty?' => true,
        ];
    }

    public function __toString()
    {
        return '';
    }
}
