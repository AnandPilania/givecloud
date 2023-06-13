<?php

namespace Ds\Enums;

class SocialProofType
{
    public const RECENT = 'recent';
    public const LARGEST_AMOUNT = 'largest_amount';

    public static function all(): array
    {
        return [
            self::RECENT,
            self::LARGEST_AMOUNT,
        ];
    }
}
