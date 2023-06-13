<?php

namespace Ds\Enums\SocialLogin;

class SupporterProviders
{
    public const FACEBOOK = 'facebook';
    public const GOOGLE = 'google';
    public const MICROSOFT = 'microsoft';

    public static function cases(): array
    {
        return [
            self::FACEBOOK,
            self::GOOGLE,
            self::MICROSOFT,
        ];
    }
}
