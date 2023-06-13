<?php

namespace Ds\Enums;

class MemberOptinAction
{
    public const OPTIN = 'optin';
    public const OPTOUT = 'optout';

    public static function all(): array
    {
        return [
            self::OPTIN,
            self::OPTOUT,
        ];
    }
}
