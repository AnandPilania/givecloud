<?php

namespace Ds\Domain\Theming\Liquid\Filters;

use Ds\Domain\Theming\Liquid\Drops\CurrencyDrop;
use Ds\Domain\Theming\Liquid\Filters;

class MoneyFilters extends Filters
{
    /**
     * Formats the price based on the shop's HTML without currency setting.
     *
     * @param string $input
     * @param \Ds\Domain\Theming\Liquid\Drops\CurrencyDrop|string $param1
     * @param string $param2
     * @return string
     */
    public function money($input, $param1 = null, $param2 = null)
    {
        if ($param1 instanceof CurrencyDrop) {
            $currencyCode = $param1;
            $format = $param2 ?? 'default';
        } else {
            $format = $param1 ?? 'default';
            $currencyCode = $param2;
        }

        if ($this->themeService->hasTranslation("money_formats.$format")) {
            $format = $this->themeService->translate("money_formats.$format");
        }

        return money($input, $currencyCode)->format($format);
    }

    /**
     * Formats the price based on the shop's HTML with currency setting.
     *
     * @param string $input
     * @return string
     */
    public static function money_with_currency($input)
    {
        return money($input) . ' ' . currency()->code;
    }

    /**
     * Formats the price based on the shop's HTML with currency setting and
     * excludes the decimal point and trailing zeros.
     *
     * @param string $input
     * @return string
     */
    public static function money_without_trailing_zeros($input)
    {
        return numeral($input)->format('0,0');
    }

    /**
     * Formats the price using a decimal.
     *
     * @param string $input
     * @return string
     */
    public static function money_without_currency($input)
    {
        return numeral($input)->format('0,0.00');
    }
}
