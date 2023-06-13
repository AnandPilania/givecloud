<?php

namespace Ds\Domain\Commerce;

use Illuminate\Support\Arr;

class ACH
{
    /**
     * Get bank name from a routing number.
     *
     * @param string $routingNumber
     * @return string|null
     */
    public static function getBankName($routingNumber): ?string
    {
        return Arr::get(dataset('fedACHdir'), (string) $routingNumber);
    }
}
