<?php

namespace Ds\Enums;

class LedgerEntryType
{
    public const DCC = 'DCC';
    public const LINE_ITEM = 'Line Item';
    public const SHIPPING = 'Shipping';
    public const TAX = 'Tax';

    public static function labels(): array
    {
        return [
            self::LINE_ITEM => 'Line Item',
            self::DCC => 'Donor Covers Cost',
            self::SHIPPING => self::SHIPPING,
            self::TAX => 'Taxes',
        ];
    }

    public static function all(): array
    {
        return [
            self::LINE_ITEM,
            self::DCC,
            self::SHIPPING,
            self::TAX,
        ];
    }
}
