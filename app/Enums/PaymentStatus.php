<?php

namespace Ds\Enums;

class PaymentStatus
{
    public const SUCCEEDED = 'succeeded';
    public const PENDING = 'pending';
    public const FAILED = 'failed';

    public static function all(): array
    {
        return [
            self::SUCCEEDED,
            self::PENDING,
            self::FAILED,
        ];
    }
}
