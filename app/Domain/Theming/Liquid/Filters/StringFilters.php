<?php

namespace Ds\Domain\Theming\Liquid\Filters;

use Illuminate\Support\Str;
use IndefiniteArticle\IndefiniteArticle;

class StringFilters
{
    /**
     * Converts a string into CamelCase.
     *
     * @param string $input
     * @return string
     */
    public static function camelcase($input)
    {
        return Str::camel($input);
    }

    /**
     * Prefix with the appropriate indefinite article.
     *
     * @param string $input
     * @return string
     */
    public static function indefinite_article($input)
    {
        return IndefiniteArticle::A($input);
    }

    /**
     * Formats a string into a handle.
     *
     * @param string $input
     * @return string
     */
    public static function handle($input)
    {
        return Str::slug($input);
    }

    /**
     * Formats a string into a handle.
     *
     * @param string $input
     * @return string
     */
    public static function handleize($input)
    {
        return self::handle($input);
    }

    /**
     * Converts a string into an MD5 hash.
     *
     * @param string $input
     * @return string
     */
    public static function md5($input)
    {
        return md5($input);
    }

    /**
     * Converts a string into a SHA-1 hash.
     *
     * @param string $input
     * @return string
     */
    public static function sha1($input)
    {
        return sha1($input);
    }

    /**
     * Converts a string into a SHA-256 hash.
     *
     * @param string $input
     * @return string
     */
    public static function sha256($input)
    {
        return hash('sha256', $input);
    }

    /**
     * Converts a string into a SHA-1 hash using a hash message authentication code (HMAC).
     *
     * @param string $input
     * @param string $secret
     * @return string
     */
    public static function hmac_sha1($input, $secret = '')
    {
        return hash_hmac('sha1', $input, $secret);
    }

    /**
     * Converts a string into a SHA-256 hash using a hash message authentication code (HMAC).
     *
     * @param string $input
     * @param string $secret
     * @return string
     */
    public static function hmac_sha256($input, $secret = '')
    {
        return hash_hmac('sha256', $input, $secret);
    }

    /**
     * Outputs the singular or plural version of a string based on the value of a number.
     *
     * @param string $input
     * @param string $singular
     * @param string $plural
     * @return string
     */
    public static function pluralize($input, $singular = '', $plural = '')
    {
        return abs($input) > 0 ? $singular : $plural;
    }

    /**
     * Add's a possessive apostrophe to a word. (Josh's or James')
     *
     * @param string $input
     * @return string
     */
    public static function possesses($input)
    {
        return substr($input, -1) == 's' ? "$input'" : "$input's";
    }

    /**
     * Escapes a string.
     *
     * @param string $input
     * @return string
     */
    public static function escape($input)
    {
        return e($input);
    }

    /**
     * Escapes a string.
     *
     * (Deprecated) Only still here for unlocked themes.
     *
     * @param string $input
     * @return string
     */
    public static function attr_escape($input)
    {
        return e($input);
    }
}
