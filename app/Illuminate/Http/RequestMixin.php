<?php

namespace Ds\Illuminate\Http;

use Closure;

/** @mixin \Illuminate\Http\Request */
class RequestMixin
{
    /**
     * Retrieve request input as array of values.
     */
    public function arrayInput()
    {
        return function ($key, $default = []) {
            $value = $this->input($key, $default);

            return is_array($value) ? $value : $default;
        };
    }

    /**
     * Retrieve request input as integer value.
     */
    public function nonArrayInput()
    {
        return function ($key, $default = null) {
            $value = $this->input($key, $default);

            return is_array($value) ? $default : $value;
        };
    }

    public function referer(): Closure
    {
        return function (): ?string {
            return $this->server('HTTP_REFERER') ?: null;
        };
    }
}
