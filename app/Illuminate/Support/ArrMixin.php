<?php

namespace Ds\Illuminate\Support;

use Illuminate\Support\Arr;

/** @mixin \Illuminate\Support\Arr */
class ArrMixin
{
    /**
     * Initialize an array using defaults.
     */
    public function defaults()
    {
        return function (array $array, array $defaults, $strict = true) {
            if ($strict) {
                $array = Arr::only($array, array_keys($defaults));
            }

            return array_merge($defaults, $array);
        };
    }
}
