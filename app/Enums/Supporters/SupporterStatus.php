<?php

namespace Ds\Enums\Supporters;

class SupporterStatus
{
    public const ARCHIVED = 0;
    public const ACTIVE = 1;
    public const SPAM = 3;
    public const ALL = 2;

    public static function all(): array
    {
        return [
            self::ARCHIVED => 'Archived',
            self::ACTIVE => 'Not archived',
            self::SPAM => 'Spam/Fraud',
            self::ALL => 'All Accounts',
        ];
    }
}
