<?php

namespace Ds\Common;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Throwable;

/**
 * Basic port of core Numeral.js functionality.
 *
 * @see https://github.com/adamwdraper/Numeral-js/blob/master/LICENSE
 */
class Numeral
{
    /** @var array */
    protected $locale = [
        'delimiters' => [
            'thousands' => ',',
            'decimal' => '.',
        ],
        'abbreviations' => [
            'thousand' => 'k',
            'million' => 'm',
            'billion' => 'b',
            'trillion' => 't',
        ],
        'currency' => [
            'symbol' => '$',
        ],
    ];

    /** @var mixed */
    protected $input;

    /** @var mixed */
    protected $value;

    /**
     * Create an instance.
     *
     * @param mixed $value
     */
    public function __construct($value)
    {
        $this->setupLocalization();

        $this->input = $value;

        try {
            $this->value = is_float($value) ? $value : $this->parseFloat((string) $value);
        } catch (Throwable $e) {
            $this->value = null;
        }
    }

    /**
     * Parse a float value.
     *
     * @param mixed $value
     * @return float
     */
    private function parseFloat($value): float
    {
        $value = trim(
            (string) $value,
            $this->localeData('currency.symbol') . " \t\n\r\0\x0B"
        );

        $t = preg_quote($this->localeData('delimiters.thousands'));
        $d = preg_quote($this->localeData('delimiters.decimal'));

        if (preg_match("/^[0-9]+(?:{$t}[0-9]{3})*(?:{$d}[0-9]*|)$/", $value)) {
            $value = str_replace($t, '', $value);
            $value = str_replace($d, '.', $value);
        }

        if ($value === '' || preg_match('/[^0-9.\-]/', $value)) {
            throw new InvalidArgumentException;
        }

        return (float) $value;
    }

    /**
     * Formats numbers separators, decimals places, signs, abbreviations
     *
     * @param string $format
     * @param array $conditions
     * @param int $roundingMode
     * @return string
     */
    public function format($format, array $conditions = [], $roundingMode = PHP_ROUND_HALF_UP)
    {
        // check for conditional formatting
        foreach ($conditions as $fmt => $condition) {
            if ($condition) {
                $format = $fmt;
                break;
            }
        }

        if (preg_match('/(^|[0\s])i?b/', $format)) {
            return $this->formatBytes($this->value, $format, $roundingMode);
        }

        return $this->numberToFormat($this->value, $format, $roundingMode);
    }

    /**
     * Formats numbers separators, decimals places, signs, abbreviations
     *
     * @param float|null $value
     * @param string $format
     * @param int $roundingMode
     * @return string
     */
    private function numberToFormat($value, $format, $roundingMode = PHP_ROUND_HALF_UP)
    {
        $negP = false;
        $optDec = false;
        $abbr = '';
        $abbrUpper = false;
        $trillion = 1000000000000;
        $billion = 1000000000;
        $million = 1000000;
        $thousand = 1000;
        $decimal = '';
        $neg = false;
        $signed = -1;

        // make sure we never format a null value
        $value = (float) $value;

        $abs = abs($value);

        // assume a numeric format is specifing precision
        if (preg_match('/^(0|[1-9][0-9]*)$/', $format)) {
            if ($format) {
                $format = '0,0.' . str_repeat('0', abs($format));
            } else {
                $format = '0,0';
            }
        }

        // see if we should use parentheses for negative number or if we should prefix with a sign
        // if both are present we default to parentheses
        if (Str::contains($format, '(')) {
            $negP = true;
            $format = preg_replace('/[\(|\)]/', '', $format);
        } elseif (Str::contains($format, ['+', '-'])) {
            $signed = Str::contains($format, '+') ? strpos($format, '+') : ($value < 0 ? strpos($format, '-') : false);
            $signed = $signed === false ? -1 : $signed;
            $format = preg_replace('/[\+|\-]/', '', $format);
        }

        // see if uppercase abbreviation is wanted
        if (Str::contains($format, 'A')) {
            $format = preg_replace('/A(k|m|b|t)?/', 'a$1', $format);
            $abbrUpper = true;
        }

        // see if abbreviation is wanted
        if (Str::contains($format, 'a')) {
            preg_match('/a(k|m|b|t)?/', $format, $abbrForce);

            $abbrForce = Arr::get($abbrForce, 1, false);

            // check for space before abbreviation
            if (Str::contains($format, ' a')) {
                $abbr = ' ';
            }

            $format = preg_replace("/{$abbr}a[kmbt]?/", '', $format);

            if ($abs >= $trillion && ! $abbrForce || $abbrForce === 't') {
                // trillion
                $abbr .= $this->localeData('abbreviations.trillion');
                $value = $value / $trillion;
            } elseif ($abs < $trillion && $abs >= $billion && ! $abbrForce || $abbrForce === 'b') {
                // billion
                $abbr .= $this->localeData('abbreviations.billion');
                $value = $value / $billion;
            } elseif ($abs < $billion && $abs >= $million && ! $abbrForce || $abbrForce === 'm') {
                // million
                $abbr .= $this->localeData('abbreviations.million');
                $value = $value / $million;
            } elseif ($abs < $million && $abs >= $thousand && ! $abbrForce || $abbrForce === 'k') {
                // thousand
                $abbr .= $this->localeData('abbreviations.thousand');
                $value = $value / $thousand;
            }

            if ($abbrUpper) {
                $abbr = strtoupper($abbr);
            }
        }

        // check for optional decimals
        if (Str::contains($format, '[.]')) {
            $optDec = true;
            $format = str_replace('[.]', '.', $format);
        }

        // break number and format
        $int = explode('.', (string) $value)[0];
        $precision = preg_replace('/[^0\[\]]/', '', Arr::get(explode('.', $format), 1, ''));
        $thousands = strpos($format, ',');
        $thousands = $thousands === false ? -1 : $thousands;
        $leadingCount = (int) preg_match_all('/0/', explode(',', explode('.', $format)[0])[0]);

        if (strlen($precision)) {
            if (Str::contains($precision, '[')) {
                $precision = str_replace(']', '', $precision);
                $precision = explode('[', $precision);
                $decimal = $this->toFixed($value, strlen($precision[0]) + strlen($precision[1]), $roundingMode, strlen($precision[1]));
            } else {
                $decimal = $this->toFixed($value, strlen($precision), $roundingMode);
            }

            $int = explode('.', $decimal)[0];

            if (Str::contains($decimal, '.')) {
                $decimal = $this->localeData('delimiters.decimal') . explode('.', $decimal)[1];
            } else {
                $decimal = '';
            }

            if ($optDec && (int) (substr($decimal, 1)) === 0) {
                $decimal = '';
            }
        } else {
            $int = $this->toFixed($value, 0, $roundingMode);
        }

        // check abbreviation again after rounding
        if ($abbr && ! $abbrForce && $int >= 1000 && $abbr !== $this->localeData('abbreviations.trillion')) {
            $int = (string) ($int / 1000);

            switch ($abbr) {
                case $this->localeData('abbreviations.thousand'):
                    $abbr = $this->localeData('abbreviations.million');
                    break;
                case $this->localeData('abbreviations.million'):
                    $abbr = $this->localeData('abbreviations.billion');
                    break;
                case $this->localeData('abbreviations.billion'):
                    $abbr = $this->localeData('abbreviations.trillion');
                    break;
            }
        }

        // format number
        if (Str::contains($int, '-')) {
            $int = substr($int, 1);
            $neg = true;
        }

        if (strlen($int) < $leadingCount) {
            for ($i = $leadingCount - strlen($int); $i > 0; $i--) {
                $int = '0' . $int;
            }
        }

        if ($thousands > -1) {
            $int = preg_replace('/(\d)(?=(\d{3})+(?!\d))/', '$1' . $this->localeData('delimiters.thousands'), $int);
        }

        if (strpos($format, '.') === 0) {
            $int = '';
        }

        $output = $int . $decimal . ($abbr ? $abbr : '');

        if ($negP) {
            $output = ($negP && $neg ? '(' : '') . $output . ($negP && $neg ? ')' : '');
        } else {
            if ($signed >= 0) {
                $output = $signed === 0 ? ($neg ? '-' : '+') . $output : $output . ($neg ? '-' : '+');
            } elseif ($neg) {
                $output = '-' . $output;
            }
        }

        return $output;
    }

    /**
     * Formats bytes
     *
     * @param float|null $value
     * @param string $format
     * @param int $roundingMode
     * @return string
     */
    private function formatBytes($value, $format, $roundingMode = PHP_ROUND_HALF_UP)
    {
        $binary = [
            'base' => 1024,
            'suffixes' => ['B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB'],
        ];

        $decimal = [
            'base' => 1000,
            'suffixes' => ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'],
        ];

        $bytes = Str::contains($format, 'ib') ? $binary : $decimal;
        $suffix = Str::contains($format, ' b') || Str::contains($format, ' ib') ? ' ' : '';

        // check for space before
        $format = preg_replace('/\s?i?b/', '', $format);

        for ($power = 0; $power <= count($bytes['suffixes']); $power++) {
            $min = $bytes['base'] ** $power;
            $max = $bytes['base'] ** ($power + 1);

            if ($value === null || $value === 0 || $value >= $min && $value < $max) {
                $suffix .= $bytes['suffixes'][$power];

                if ($min > 0) {
                    $value = $value / $min;
                }

                break;
            }
        }

        $output = $this->numberToFormat($value, $format, $roundingMode);

        return $output . $suffix;
    }

    private function setupLocalization(): void
    {
        $locale = trans('numeral');

        if (is_array($locale)) {
            $this->locale = $locale;
        }

        Arr::set($this->locale, 'currency.symbol', currency()->getSymbol());
    }

    /**
     * Retrive locale data.
     *
     * @param string|null $key
     * @return mixed
     */
    private function localeData($key = null)
    {
        if ($key === null) {
            return $this->locale;
        }

        return data_get($this->locale, $key);
    }

    /**
     * Implementation of toFixed() that treats floats more like decimals
     *
     * Fixes binary rounding issues (eg. (0.615).toFixed(2) === '0.61') that present
     * problems for accounting- and finance-related software.
     *
     * @param mixed $value
     * @param int $maxDecimals
     * @param int $roundingMode
     * @param int $optionals
     * @return string
     */
    private function toFixed($value, $maxDecimals = 2, $roundingMode = PHP_ROUND_HALF_UP, $optionals = 0)
    {
        $splitValue = explode('.', (string) $value);
        $minDecimals = $maxDecimals - $optionals;

        // Use the smallest precision value possible to avoid errors from floating point representation
        if (count($splitValue) === 2) {
            $boundedPrecision = min(max(strlen($splitValue[1]), $minDecimals), $maxDecimals);
        } else {
            $boundedPrecision = $minDecimals;
        }

        $power = 10 ** $boundedPrecision;

        // Multiply up by precision, round accurately, then divide and use native toFixed():
        $output = number_format(round($value . 'e+' . $boundedPrecision) / $power, $boundedPrecision, '.', '');

        if ($optionals > $maxDecimals - $boundedPrecision) {
            $optionalsRegExp = '/\\.?0{1,' . ($optionals - ($maxDecimals - $boundedPrecision)) . '}$/';
            $output = preg_replace($optionalsRegExp, '', $output);
        }

        return $output;
    }

    /**
     * Output as float.
     *
     * @return float|null
     */
    public function toFloat(): ?float
    {
        return $this->value;
    }

    /**
     * Output as string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->format('0,0.00');
    }
}
