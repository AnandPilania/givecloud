<?php

namespace Ds\Domain\Theming;

use Primal\Color\Color as PrimalColor;
use Primal\Color\Parser;

class Color extends PrimalColor
{
    /**
     * Parse string representation of a color with invalid colors being returned
     * as a blank/null Color object.
     *
     * @param string $input
     */
    public static function parse($input)
    {
        try {
            return Parser::Parse($input);
        } catch (\Exception $e) {
            return new self;
        }
    }

    /**
     * Parse string representation of a color with invalid colors being returned
     * as a blank/null Color object.
     *
     * @param string $output
     */
    public static function output($output)
    {
        if (\SSNepenthe\ColorUtils\alpha($output) < 1.0) {
            return self::parse($output)->toCSS();
        }

        return self::parse($output)->toHex();
    }

    /**
     * @return \Ds\Domain\Theming\Color
     */
    public function toHSV()
    {
        return $this;
    }

    /**
     * @return \Ds\Domain\Theming\Color
     */
    public function toHSL()
    {
        return $this;
    }

    /**
     * @return \Ds\Domain\Theming\Color
     */
    public function toRGB()
    {
        return $this;
    }

    /**
     * @return \Ds\Domain\Theming\Color
     */
    public function toCMYK()
    {
        return $this;
    }

    /**
     * @return string
     */
    public function toCSS()
    {
        return '';
    }

    /**
     * @return string
     */
    public function toHex()
    {
        return '';
    }
}
