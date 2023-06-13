<?php

namespace Ds\Domain\Theming\Liquid\Drops;

use Ds\Domain\Theming\Liquid\Drop;

class TaxLineDrop extends Drop
{
    /**
     * Create an instance.
     *
     * @param array $source
     */
    public function __construct(array $source)
    {
        $this->liquid = [
            'title' => $source['code'],
            'price' => $source['price'],
            'rate' => round($source['rate'] / 100, 4),
            'rate_percentage' => $source['rate'],
        ];
    }
}
