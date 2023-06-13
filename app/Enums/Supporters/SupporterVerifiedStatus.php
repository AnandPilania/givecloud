<?php

namespace Ds\Enums\Supporters;

class SupporterVerifiedStatus
{
    public const DENIED = 'Denied';
    public const PENDING = 'Pending';
    public const VERIFIED = 'Verified';

    public static function all(): array
    {
        return [
            self::DENIED,
            self::PENDING,
            self::VERIFIED,
        ];
    }
}
