<?php

namespace Ds\Enums;

class StockAdjustmentState
{
    public const IN_STOCK = 'in_stock';
    public const SOLD = 'sold';

    public static function all(): array
    {
        return [
            self::IN_STOCK,
            self::SOLD,
        ];
    }
}
