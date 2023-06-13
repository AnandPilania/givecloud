<?php

namespace Ds\Enums\ExternalReference;

class ExternalReferenceType
{
    public const DCC = 'DCC';
    public const ITEM = 'ITEM';
    public const ORDER = 'ORDER';
    public const PLEDGE = 'PLEDGE';
    public const TXN = 'TXN';
    public const TXNSPLIT = 'TXNSPLIT';
    public const SHIPPING = 'SHIP';
    public const SUPPORTER = 'SUPPORTER';
    public const TAX = 'TAX';

    public static function cases(): array
    {
        return [
            self::DCC,
            self::ITEM,
            self::ORDER,
            self::PLEDGE,
            self::TXN,
            self::TXNSPLIT,
            self::SUPPORTER,
            self::SHIPPING,
            self::TAX,
        ];
    }
}
