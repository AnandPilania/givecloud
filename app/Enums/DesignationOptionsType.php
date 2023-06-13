<?php

namespace Ds\Enums;

class DesignationOptionsType
{
    public const SINGLE_ACCOUNT = 'single_account';
    public const SUPPORTERS_CHOICE = 'supporters_choice';

    public static function all(): array
    {
        return [
            self::SINGLE_ACCOUNT,
            self::SUPPORTERS_CHOICE,
        ];
    }
}
