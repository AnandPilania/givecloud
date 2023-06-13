<?php

namespace Ds\Enums;

class FundraisingPageType
{
    public const STANDALONE = 'standalone';
    public const WEBSITE = 'website';

    public static function all(): array
    {
        return [
            self::STANDALONE,
            self::WEBSITE,
        ];
    }
}
