<?php

namespace Ds\Enums;

class StockAdjustmentType
{
    public const ADJUSTMENT = 'adjustment';
    public const PHYSICAL_COUNT = 'physical_count';

    public static function all(): array
    {
        return [
            self::ADJUSTMENT,
            self::PHYSICAL_COUNT,
        ];
    }
}
