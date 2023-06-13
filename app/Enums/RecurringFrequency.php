<?php

namespace Ds\Enums;

class RecurringFrequency
{
    public const WEEKLY = 'weekly';
    public const BIWEEKLY = 'biweekly';
    public const MONTHLY = 'monthly';
    public const QUARTERLY = 'quarterly';
    public const BIANNUALLY = 'biannually';
    public const ANNUALLY = 'annually';

    public static function all(): array
    {
        return [
            self::WEEKLY,
            self::BIWEEKLY,
            self::MONTHLY,
            self::QUARTERLY,
            self::BIANNUALLY,
            self::ANNUALLY,
        ];
    }
}
