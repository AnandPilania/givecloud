<?php

namespace Ds\Enums;

class CardType
{
    public const CREDIT = 'credit';
    public const DEBIT = 'debit';
    public const PREPAID = 'prepaid';
    public const UNKNOWN = 'unknown';

    public static function all(): array
    {
        return [
            self::CREDIT,
            self::DEBIT,
            self::PREPAID,
            self::UNKNOWN,
        ];
    }
}
