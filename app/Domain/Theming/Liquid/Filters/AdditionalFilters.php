<?php

namespace Ds\Domain\Theming\Liquid\Filters;

use Carbon\Carbon;
use Ds\Domain\Theming\Liquid\Drop;
use Ds\Domain\Theming\Liquid\Filters;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Throwable;

class AdditionalFilters extends Filters
{
    /**
     * Check if item is in the current cart.
     *
     * @param mixed $input
     * @return int
     */
    public static function in_cart($input)
    {
        if (is_object($input)) {
            if ($input instanceof \Ds\Domain\Theming\Liquid\Drops\SponseeDrop) {
                return cart()->items()->where('sponsorship_id', $input->id)->count();
            }

            if ($input instanceof \Ds\Domain\Theming\Liquid\Drops\VariantDrop) {
                return cart()->items()->where('productinventoryid', $input->id)->count();
            }

            if ($input instanceof \Ds\Domain\Theming\Liquid\Drops\ProductDrop) {
                return cart()->products()->where('code', $input->code)->count();
            }
        }

        return 0;
    }

    /**
     * Converts a timestamp into another date format.
     *
     * @param string $input
     * @param string $format
     * @param bool $createFromFormat
     * @return string
     */
    public function date($input, $format = 'auto', $createFromFormat = null)
    {
        if (Str::startsWith($format, 'date:')) {
            $input = \Ds\Domain\Shared\Date::parse($input);
            $format = trim(Str::after($format, 'date:'));
        }

        if (is_string($input) && preg_match('/^\d\d\d\d-\d\d-\d\d$/', $input)) {
            $input = \Ds\Domain\Shared\Date::parse($input);
        }

        if (is_string($input) && $createFromFormat) {
            if ($createFromFormat === 'w') {
                return Arr::get(Carbon::getDays(), $input);
            }

            if ($createFromFormat === 'N') {
                return Arr::get(Carbon::getDays(), $input == 7 ? 0 : $input);
            }

            try {
                $input = Carbon::createFromFormat($createFromFormat, $input);
            } catch (Throwable $e) {
                $input = null;
            }
        }

        if ($this->themeService->hasTranslation("date_formats.$format")) {
            $format = $this->themeService->translate("date_formats.$format");
        }

        return toLocalFormat($input, $format);
    }

    /**
     * Converts a timestamp into another date format.
     *
     * @param string $input
     * @param string $format
     * @return string
     */
    public static function ordinal($input, $format = null)
    {
        if (is_numeric($input)) {
            $output = (new \NumberFormatter('en', \NumberFormatter::ORDINAL))->format($input);
        } else {
            $output = toLocalFormat($input, 'jS');
        }

        if ($format) {
            return preg_replace('/\d*/', '', $output);
        }

        return $output;
    }

    /**
     * Dumps information about a template variable.
     *
     * @param mixed $input
     * @return string
     */
    public static function dump($input)
    {
        ob_start();

        $data = Drop::resolveData($input);
        $data = json_decode(json_encode($data));

        dump($data); // phpcs:ignore

        return ob_get_clean();
    }

    /**
     * Dumps information about a template variable.
     *
     * @param mixed $input
     * @return mixed
     */
    public static function ray($input)
    {
        if (function_exists('ray')) {
            ray($input); // phpcs:ignore
        }

        return $input;
    }

    /**
     * The time_tag filter converts a timestamp into an HTML <time> tag.
     *
     * @param string $input
     * @param array|string $format
     * @return string
     */
    public static function time_tag($input, $format = 'r')
    {
        $d = toLocal($input);

        if ($d) {
            $datetime = 'api';

            if (is_array($format)) {
                $datetime = Arr::get($format, 'datetime', $datetime);
                $format = Arr::get($format, 'format', 'r');
            }

            return sprintf('<time datetime="%s">%s</time>', formatDateTime($d, $datetime), formatDateTime($d, $format));
        }

        return '';
    }

    /**
     * Outputs default error messages for the form.errors variable.
     *
     * @param string $input
     * @return string
     */
    public static function default_errors($input)
    {
        return '';
    }

    /**
     * Creates a set of links for paginated results.
     *
     * @param string $input
     * @return string
     */
    public static function default_pagination($input)
    {
        return '';
    }

    /**
     * Print the elements of the address in order according to their locale.
     *
     * @param \Ds\Domain\Theming\Liquid\Drops\AddressDrop $input
     * @return string
     */
    public static function format_address($input)
    {
        if (is_object($input) && $input instanceof \Ds\Domain\Theming\Liquid\Drops\AddressDrop) {
            $lines = collect([
                $input->name,
                $input->address1,
                $input->address2,
                $input->city,
                $input->province_code,
                $input->zip,
                $input->country,
            ])->reject(function ($line) {
                return trim((string) $line) === '';
            })->map(function ($line) {
                return "\t{$line}<br>\n";
            })->implode('');

            return "<p>$lines</p>";
        }
    }

    /**
     * Wraps words inside string with an HTML <strong> tag with the class highlight.
     *
     * @param string $input
     * @param string $terms
     * @return string
     */
    public static function highlight($input, $terms = null)
    {
        if ($terms) {
            return preg_replace('/(' . preg_quote($terms) . ')/mi', '<strong class="highlight">$1</strong>', $input);
        }

        return $input;
    }

    /**
     * Wraps a tag link in a <span> with the class active if that tag is being used to filter a collection.
     *
     * @param string $input
     * @return string
     */
    public static function highlight_active_tag($input)
    {
        return $input;
    }

    /**
     * Converts a string into JSON format.
     *
     * @param string $input
     * @param string $option
     * @return string
     */
    public static function json($input, $option = null)
    {
        if ($option === 'pretty') {
            return @json_encode($input, JSON_PRETTY_PRINT);
        }

        return @json_encode($input);
    }

    /**
     * Formats the product variant's weight.
     *
     * @param string $input
     * @param string $unit
     * @return string
     */
    public static function weight_with_unit($input, $unit = 'lb')
    {
        return "$input $unit";
    }

    /**
     * Takes a placeholder name and outputs a placeholder SVG illustration.
     *
     * @param string $input
     * @param string $classes
     * @return string
     */
    public static function placeholder_svg_tag($input, $classes = '')
    {
        return '';
    }

    /**
     * @param string $input
     * @return string
     */
    public static function display_shortcode($input)
    {
        return do_shortcode($input);
    }
}
