<?php

namespace Ds\Enums;

class VirtualEventThemeStyle
{
    public const DARK = 'dark';
    public const LIGHT = 'light';

    public static function all(): array
    {
        return [
            self::DARK,
            self::LIGHT,
        ];
    }

    public static function default(): string
    {
        return self::DARK;
    }

    public static function labels(): array
    {
        return [
            self::DARK => 'Dark',
            self::LIGHT => 'Light',
        ];
    }
}
