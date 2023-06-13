<?php

namespace Ds\Enums;

class RecurringPaymentProfileStatus
{
    public const ACTIVE = 'Active';
    public const CANCELLED = 'Cancelled';
    public const EXPIRED = 'Expired';
    public const SUSPENDED = 'Suspended';

    public static function all(): array
    {
        return [
            self::ACTIVE,
            self::CANCELLED,
            self::EXPIRED,
            self::SUSPENDED,
        ];
    }
}
