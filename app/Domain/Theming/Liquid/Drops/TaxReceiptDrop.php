<?php

namespace Ds\Domain\Theming\Liquid\Drops;

use Ds\Domain\Theming\Liquid\Drop;

class TaxReceiptDrop extends Drop
{
    protected function initialize($source)
    {
        $this->liquid = [
            'id' => $source->id,
            'number' => $source->number,
            'issued_at' => $source->issued_at,
            'ordered_at' => $source->ordered_at,
            'amount' => $source->amount,
        ];
    }
}
