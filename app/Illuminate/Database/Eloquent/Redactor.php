<?php

namespace Ds\Illuminate\Database\Eloquent;

use OwenIt\Auditing\Contracts\AttributeRedactor;

class Redactor implements AttributeRedactor
{
    /**
     * {@inheritdoc}
     */
    public static function redact($value): string
    {
        $total = strlen($value);

        return str_repeat('*', min(32, $total));
    }
}
