<?php

namespace Ds\Domain\Theming\Liquid\Filters;

class HTMLFilters
{
    /**
     * Generates an image tag.
     *
     * @param mixed $input
     * @param string $alt
     * @param string $classes
     * @param string $size
     * @return string
     */
    public static function img_tag($input, $alt = null, $classes = null, $size = null)
    {
        // product
        // variant
        // line item
        // collection
        // image

        $img = ['src="' . e($input) . '"'];

        if ($alt) {
            $img[] = 'alt="' . e($alt) . '"';
        }

        if ($classes) {
            $img[] = 'class="' . e($classes) . '"';
        }

        return '<img ' . implode(' ', $img) . ' />';
    }

    /**
     * Generates a script tag.
     *
     * @param string $input
     * @return string
     */
    public static function script_tag($input)
    {
        return '<script src="' . e($input) . '" type="text/javascript"></script>';
    }

    /**
     * Generates a stylesheet tag.
     *
     * @param string $input
     * @return string
     */
    public static function stylesheet_tag($input)
    {
        return '<link href="' . e($input) . '" rel="stylesheet" type="text/css" media="all" />';
    }
}
