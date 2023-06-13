<?php

namespace Ds\Domain\Commerce\Enums;

class ContributionPaymentType
{
    public const BANK_ACCOUNT = 'bank_account';
    public const CREDIT_CARD = 'credit_card';
    public const PAYMENT_METHOD = 'payment_method';
    public const PAYPAL = 'paypal';
    public const WALLET_PAY = 'wallet_pay';

    public static function cases(): array
    {
        return [
            self::BANK_ACCOUNT,
            self::CREDIT_CARD,
            self::PAYMENT_METHOD,
            self::PAYPAL,
            self::WALLET_PAY,
        ];
    }
}
