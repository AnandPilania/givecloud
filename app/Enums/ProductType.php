<?php

namespace Ds\Enums;

class ProductType
{
    public const TEMPLATE = 'template';
    public const DONATION_FORM = 'donation_form';

    public static function cases(): array
    {
        return [
            self::TEMPLATE,
            self::DONATION_FORM,
        ];
    }
}
