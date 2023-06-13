<?php

namespace Ds\Domain\Theming\Liquid\Filters;

class ArrayFilters
{
    /**
     * Concatenates (combines) an array with another array.
     *
     * @param array $input
     * @param array $data
     * @return array
     */
    public static function concat($input, $data)
    {
        return collect($input)->merge($data)->all();
    }

    /**
     * Breaks the array into multiple, smaller arrays of a given size.
     *
     * @param array $input
     * @param int $size
     * @return array
     */
    public static function chunk($input, $size = null)
    {
        if (is_numeric($size)) {
            return collect($input)
                ->chunk((int) $size)
                ->map(function ($chunk) {
                    return $chunk->values()->all();
                })->all();
        }

        return $input;
    }

    /**
     * Creates an array including only the objects with a given property value, or any truthy value by default.
     *
     * @param array $input
     * @param string $key
     * @param mixed $condition
     * @return array
     */
    public static function where($input, $key, $condition = null)
    {
        return collect($input)->filter(function ($item) use ($key, $condition) {
            $value = $item[$key] ?? $item->{$key} ?? null;

            return ($condition === null) ? (bool) $value : ($value == $condition);
        })->all();
    }
}
