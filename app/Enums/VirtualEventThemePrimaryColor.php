<?php

namespace Ds\Enums;

class VirtualEventThemePrimaryColor
{
    public const BLUE = 'blue';
    public const GREEN = 'green';
    public const INDIGO = 'indigo';
    public const PURPLE = 'purple';
    public const PINK = 'pink';

    public static function all(): array
    {
        return [
            self::BLUE,
            self::GREEN,
            self::INDIGO,
            self::PURPLE,
            self::PINK,
        ];
    }

    public static function default(): string
    {
        return self::INDIGO;
    }

    public static function labels(): array
    {
        return [
            self::BLUE => 'Blue',
            self::GREEN => 'Green',
            self::INDIGO => 'Indigo',
            self::PURPLE => 'Purple',
            self::PINK => 'Pink',
        ];
    }
}
