<?php

namespace Ds\Domain\Theming\Liquid\Filters;

class MathFilters
{
    /**
     * Returns the absolute value of a number.
     *
     * @param float $input
     * @return float
     */
    public static function abs($input)
    {
        return abs($input);
    }

    /**
     * Limits a number to a maximum value.
     *
     * @param float $input
     * @param float $amount
     * @return float
     */
    public static function at_most($input, $amount = null)
    {
        if (is_null($amount)) {
            return $input;
        }

        return min($input, (float) $amount);
    }

    /**
     * Limits a number to a minimum value.
     *
     * @param float $input
     * @param float $amount
     * @return float
     */
    public static function at_least($input, $amount = null)
    {
        if (is_null($amount)) {
            return $input;
        }

        return max($input, (float) $amount);
    }

    /**
     * Rounds an output up to the nearest integer.
     *
     * @param float $input
     * @return float
     */
    public static function ceil($input)
    {
        return ceil($input);
    }

    /**
     * Divides an output by a number.
     *
     * @param float $input
     * @param float $amount
     * @return float|void
     */
    public static function divided_by($input, $amount = null)
    {
        if (is_null($amount) || $amount == 0) {
            return;
        }

        return round($input / $amount);
    }

    /**
     * Rounds an output down to the nearest integer.
     *
     * @param float $input
     * @return float
     */
    public static function floor($input)
    {
        return floor($input);
    }

    /**
     * Subtracts a number from an output.
     *
     * @param float $input
     * @param float $amount
     * @return float
     */
    public static function minus($input, $amount = null)
    {
        return $input - $amount;
    }

    /**
     * Adds a number to an output.
     *
     * @param float $input
     * @param float $amount
     * @return float
     */
    public static function plus($input, $amount = null)
    {
        return $input + $amount;
    }

    /**
     * Multiplies an output by a number.
     *
     * @param float $input
     * @param float $amount
     * @return float
     */
    public static function times($input, $amount = null)
    {
        return $input * $amount;
    }

    /**
     * Divides an output by a number and returns the remainder.
     *
     * @param float $input
     * @param float $amount
     * @return float|void
     */
    public static function modulo($input, $amount = null)
    {
        if (is_null($amount) || $amount == 0) {
            return;
        }

        return $input % $amount;
    }
}
