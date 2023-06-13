<?php

namespace Ds\Illuminate\Support;

use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/** @mixin \Illuminate\Support\Str */
class StrMixin
{
    /**
     * Convert truthy values to boolean. Unlike typical casting to boolean all values
     * are falsy unless defined as truthy.
     */
    public function boolify()
    {
        return function ($value) {
            if ($value === true || preg_match('/^(1|t|true|y|yes)$/i', $value)) {
                return true;
            }

            return false;
        };
    }

    /**
     * Normalize gender values.
     */
    public function genderize()
    {
        return function ($value) {
            if (preg_match('/^(b|boy|gentleman|gentlemen|him|his|m|male|man|men)$/i', $value)) {
                return 'M';
            }

            if (preg_match('/^(f|female|g|girl|her|hers|lady|w|woman|women)$/i', $value)) {
                return 'F';
            }

            return null;
        };
    }

    public function initials(): Closure
    {
        return function (?string $value): ?string {
            if (empty($value)) {
                return $value;
            }

            $words = preg_split('/(\s|-|_)/u', Str::upper($value)) ?: [];

            if (count($words) > 1) {
                $firstInitial = mb_substr($words[0], 0, 1, 'UTF-8');
                $lastInitial = mb_substr(Arr::last($words), 0, 1, 'UTF-8');

                return $firstInitial . $lastInitial;
            }

            return Str::substr($words[0], 0, 2);
        };
    }

    public function firstName(): Closure
    {
        return function (?string $value): ?string {
            $parts = explode(' ', $value);
            array_pop($parts);

            return implode(' ', $parts) ?: null;
        };
    }

    public function lastName(): Closure
    {
        return function (?string $value): ?string {
            $parts = explode(' ', $value);

            return array_pop($parts) ?: null;
        };
    }

    /**
     * Make a string possessive.
     */
    public function possessive()
    {
        return function ($value) {
            if (empty($value)) {
                return '';
            }

            return $value . '\'' . ($value[strlen($value) - 1] != 's' ? 's' : '');
        };
    }

    /**
     * Generate UUID (version 4) as an integer.
     */
    public function uuid64()
    {
        return function () {
            return Str::crc64((string) Str::uuid(), '%u');
        };
    }

    /**
     * Calculates the crc64 polynomial of a string
     */
    public function crc64()
    {
        return function ($string, $format = '%x') {
            static $crc64tab;

            if (empty($crc64tab)) {
                $crc64tab = [];
                $poly64rev = (0xC96C5795 << 32) | 0xD7870F42; // ECMA polynomial

                for ($i = 0; $i < 256; $i++) {
                    for ($part = $i, $bit = 0; $bit < 8; $bit++) {
                        if ($part & 1) {
                            $part = (($part >> 1) & ~(0x8 << 60)) ^ $poly64rev;
                        } else {
                            $part = ($part >> 1) & ~(0x8 << 60);
                        }
                    }

                    $crc64tab[$i] = $part;
                }
            }

            $crc = 0;

            for ($i = 0; $i < strlen($string); $i++) {
                $crc = $crc64tab[($crc ^ ord($string[$i])) & 0xff] ^ (($crc >> 8) & ~(0xff << 56));
            }

            return sprintf($format, $crc);
        };
    }
}
