<?php

namespace Ds\Enums;

class PaymentType
{
    public const BANK = 'bank';
    public const CARD = 'card';
    public const CASH = 'cash';
    public const CHEQUE = 'cheque';
    public const PAYPAL = 'paypal';
    public const UNKNOWN = 'unknown';

    public static function all(): array
    {
        return [
            self::BANK,
            self::CARD,
            self::CASH,
            self::CHEQUE,
            self::PAYPAL,
            self::UNKNOWN,
        ];
    }
}
