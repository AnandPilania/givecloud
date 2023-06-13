<?php

namespace Ds\Enums;

class MemberOptinSource
{
    public const BACKFILL = 'backfill'; // only used in migration
    public const CHECKOUT = 'checkout';
    public const CHECKOUT_NAG = 'checkout_nag';
    public const DONOR_PERFECT = 'donor_perfect';
    public const IMPORT = 'import';
    public const WEBSITE = 'website';

    public static function all(): array
    {
        return [
            self::CHECKOUT,
            self::CHECKOUT_NAG,
            self::DONOR_PERFECT,
            self::IMPORT,
            self::WEBSITE,
        ];
    }
}
