<?php

namespace Ds\Domain\Commerce\Enums;

class CredentialOnFileInitiatedBy
{
    public const CUSTOMER = 'customer';
    public const MERCHANT = 'merchant';

    public static function all(): array
    {
        return [
            self::CUSTOMER,
            self::MERCHANT,
        ];
    }
}
