<?php

namespace Ds\Enums;

class NodeType
{
    public const ADVANCED = 'advanced';
    public const CATEGORY = 'category';
    public const HTML = 'html';
    public const LIQUID = 'liquid';
    public const MENU = 'menu';
    public const REVISION = 'revision';

    public static function all(): array
    {
        return [
            self::ADVANCED,
            self::CATEGORY,
            self::HTML,
            self::LIQUID,
            self::MENU,
        ];
    }
}
