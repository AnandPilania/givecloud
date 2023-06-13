<?php

namespace Ds\Domain\Zapier\Enums;

class Events
{
    public const CONTRIBUTION_PAID = 'contribution.paid';
    public const SUPPORTER_CREATED = 'supporter.created';
    public const SUPPORTER_UPDATED = 'supporter.updated';

    public static function cases(): array
    {
        return [
            self::CONTRIBUTION_PAID,
            self::SUPPORTER_CREATED,
            self::SUPPORTER_UPDATED,
        ];
    }
}
