<?php

namespace Ds\Domain\Theming\Liquid\Filters;

use Ds\Domain\Theming\Color;

class ColorFilters
{
    /**
     * Converts a CSS color string to CSS rgb() format.
     *
     * @param string $input
     * @return string
     */
    public static function color_to_rgb($input)
    {
        return Color::parse($input)->toRGB()->toCSS();
    }

    /**
     * Converts a CSS color string to CSS hsl() format.
     *
     * @param string $input
     * @return string
     */
    public static function color_to_hsl($input)
    {
        return Color::parse($input)->toHSL()->toCSS();
    }

    /**
     * Converts a CSS color string to hex6 format.
     *
     * @param string $input
     * @return string
     */
    public static function color_to_hex($input)
    {
        return Color::parse($input)->toRGB()->toHex();
    }

    /**
     * Extracts a component from the color. Valid components are alpha, red, green, blue, hue,
     * saturation and lightness.
     *
     * @param string $input
     * @param string $component
     * @return float|int|string
     */
    public static function color_extract($input, $component = null)
    {
        $input = Color::parse($input)->toRGB()->toHex();

        if ($input) {
            switch ($component) {
                case 'alpha':      return \SSNepenthe\ColorUtils\alpha($input);
                case 'red':        return \SSNepenthe\ColorUtils\red($input);
                case 'green':      return \SSNepenthe\ColorUtils\green($input);
                case 'blue':       return \SSNepenthe\ColorUtils\blue($input);
                case 'hue':        return \SSNepenthe\ColorUtils\hue($input);
                case 'saturation': return \SSNepenthe\ColorUtils\saturation($input);
                case 'lightness':  return \SSNepenthe\ColorUtils\lightness($input);
            }
        }

        return '';
    }

    /**
     * Calculates the perceived brightness of the given color.
     *
     * @param string $input
     * @return float|string
     */
    public static function color_brightness($input)
    {
        $input = Color::parse($input)->toRGB()->toHex();

        if ($input) {
            return \SSNepenthe\ColorUtils\brightness($input);
        }

        return '';
    }

    /**
     * Modifies the given component of a color.
     *
     * @param string $input
     * @param string $component
     * @param int|float $amount
     * @return string
     */
    public static function color_modify($input, $component = null, $amount = null)
    {
        $input = Color::parse($input)->toRGB()->toCSS();

        if ($input && ! is_null($component) && ! is_null($amount)) {
            if ($component === 'red' || $component === 'green' || $component === 'blue') {
                $amount = min(255, max(0, $amount));
            } elseif ($component === 'alpha') {
                $amount = min(1, max(0, $amount));
            } elseif ($component === 'hue') {
                $amount = min(360, max(0, $amount));
            } elseif ($component === 'saturation' || $component === 'lightness') {
                $amount = min(100, max(0, $amount));
            } else {
                return '';
            }

            return Color::output(\SSNepenthe\ColorUtils\change_color($input, [$component => $amount]));
        }

        return '';
    }

    /**
     * Lightens the input color.
     *
     * @param string $input
     * @param float $amount
     * @return string
     */
    public static function color_lighten($input, $amount = 0)
    {
        $input = Color::parse($input)->toRGB()->toHex();

        if ($input) {
            return Color::output(\SSNepenthe\ColorUtils\lighten($input, (float) $amount));
        }

        return '';
    }

    /**
     * Darkens the input color.
     *
     * @param string $input
     * @param float $amount
     * @return string
     */
    public static function color_darken($input, $amount = 0)
    {
        $input = Color::parse($input)->toRGB()->toHex();

        if ($input) {
            return Color::output(\SSNepenthe\ColorUtils\darken($input, (float) $amount));
        }

        return '';
    }

    /**
     * Saturates the input color.
     *
     * @param string $input
     * @param float $amount
     * @return string
     */
    public static function color_saturate($input, $amount = 0)
    {
        $input = Color::parse($input)->toRGB()->toHex();

        if ($input) {
            return Color::output(\SSNepenthe\ColorUtils\saturate($input, (float) $amount));
        }

        return '';
    }

    /**
     * Desaturates the input color.
     *
     * @param string $input
     * @param float $amount
     * @return string
     */
    public static function color_desaturate($input, $amount = 0)
    {
        $input = Color::parse($input)->toRGB()->toHex();

        if ($input) {
            return Color::output(\SSNepenthe\ColorUtils\desaturate($input, (float) $amount));
        }

        return '';
    }

    /**
     * Blends together two colors.
     *
     * @param string $input
     * @param string $color
     * @param int $weight
     * @return string
     */
    public static function color_mix($input, $color = null, $weight = 50)
    {
        $input = Color::parse($input)->toRGB()->toCSS();
        $color = Color::parse($color)->toRGB()->toCSS();

        if ($input && $color) {
            $input = \SSNepenthe\ColorUtils\color($input);
            $color = \SSNepenthe\ColorUtils\color($color);

            return Color::output(\SSNepenthe\ColorUtils\mix($input, $color, (int) $weight));
        }

        return '';
    }
}
