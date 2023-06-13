<?php

namespace Ds\Domain\Commerce;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Str;
use JsonSerializable;
use Money\Money as PhpMoney;

/**
 * @property-read float $amount
 * @property-read \Ds\Domain\Commerce\Currency $currency
 * @property-read string $currency_code
 */
class Money implements Arrayable, Jsonable, JsonSerializable
{
    /** @var \Money\Money */
    protected $value;

    /**
     * Create an instance.
     *
     * @param mixed $value
     * @param mixed $currencyCode
     */
    public function __construct($value, $currencyCode = null, $inSubunits = false)
    {
        if ($value instanceof PhpMoney) {
            $this->value = clone $value;
        } elseif ($value instanceof Money) {
            $this->value = $value->toPhpMoney();
        } else {
            $currency = new Currency($currencyCode);

            $this->value = new PhpMoney(
                $inSubunits ? $value : bcmul($value, $currency->getSubunit(), 0),
                $currency->toPhpCurrency()
            );
        }

        if ($currencyCode) {
            $currencyCode = new Currency($currencyCode);
            if ($this->currency_code !== $currencyCode->code) {
                $this->value = $this->toCurrency($currencyCode)->toPhpMoney();
            }
        }
    }

    /**
     * Format the money as a string.
     *
     * @param string $format
     * @param array $conditions
     * @return string
     */
    public function format($format = '$0,0.00', array $conditions = [])
    {
        // check for conditional formatting
        foreach ($conditions as $fmt => $condition) {
            if ($condition) {
                $format = $fmt;
                break;
            }
        }

        // assume a numeric format is specifing precision
        if (is_numeric($format)) {
            if ($format) {
                $format = '$0,0.' . str_repeat('0', abs($format));
            } else {
                $format = '$0,0';
            }
        }

        $numFormat = preg_replace('/^(?:[+\-(\s$]|\[?[$]{3}\]?)*(.*?)(?:[+\-)\s$]|\[?[$]{3}\]?)*$/', '$1', $format);

        // when doing a non-specific abbreviation and under 1k ensure value is formatted to cents
        if (preg_match('/(?:\[\.]|\.)0+[aA](?![kmbt])/', $numFormat) && $this->getAmount() < 1000) {
            $numFormat = Str::contains($numFormat, '[.]') ? '0[.]00' : '0.00';
        }

        $output = numeral($this->amount)->format($numFormat);
        $output = preg_replace('/^((?:[+\-(\s$]|\[?[$]{3}\]?)*)(?:.*?)((?:[+\-)\s$]|\[?[$]{3}\]?)*)$/', '${1}' . $output . '$2', $format);

        $currencies = Currency::getLocalCurrencies();

        if (Str::contains($format, '[$$$]')) {
            if (count($currencies) === 1 || $this->currency->has_unique_symbol) {
                $output = str_replace('[$$$]', '', $output);
            } else {
                $output = str_replace('[$$$]', $this->currency->code, $output);
            }
        }

        if (Str::contains($format, '$$$')) {
            $output = str_replace('$$$', $this->currency->code, $output);
        }

        if (Str::contains($format, '$')) {
            $output = str_replace('$', $this->currency->symbol, $output);
        }

        return trim($output);
    }

    /**
     * Get amount.
     *
     * @return float
     */
    public function getAmount(): float
    {
        return $this->getAmountInSubunits() / $this->getCurrency()->getSubunit();
    }

    /**
     * Get amount in subunits.
     */
    public function getAmountInSubunits(): int
    {
        return $this->value->getAmount();
    }

    /**
     * Get currency.
     *
     * @return \Ds\Domain\Commerce\Currency
     */
    public function getCurrency(): Currency
    {
        return new Currency($this->value->getCurrency());
    }

    /**
     * Get currency code.
     *
     * @return string
     */
    public function getCurrencyCode(): string
    {
        return strtoupper($this->value->getCurrency()->getCode());
    }

    /**
     * Get an item with a given key.
     *
     * @param mixed $key
     * @return mixed
     */
    public function __get($key)
    {
        if ($key === 'amount') {
            return $this->getAmount();
        }

        if ($key === 'currency') {
            return $this->getCurrency();
        }

        if ($key === 'currency_code') {
            return $this->getCurrencyCode();
        }

        return null;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'amount' => $this->amount,
            'currency_code' => $this->currency_code,
        ];
    }

    /**
     * Return a PHP Money object.
     *
     * @return \Money\Money
     */
    public function toPhpMoney(): PhpMoney
    {
        return clone $this->value;
    }

    /**
     * Convert to a different currency.
     *
     * @param mixed $currencyCode
     * @return \Ds\Domain\Commerce\Money
     */
    public function toCurrency($currencyCode)
    {
        $currencyCode = new Currency($currencyCode);

        if ($this->currency_code === $currencyCode->code) {
            return clone $this;
        }

        $rate = Currency::getExchangeRate($this->getCurrencyCode(), $currencyCode);

        return new Money($this->getAmount() * $rate, $currencyCode);
    }

    /**
     * Convert to default currency.
     *
     * @return \Ds\Domain\Commerce\Money
     */
    public function toDefaultCurrency()
    {
        return $this->toCurrency(Currency::getDefaultCurrencyCode());
    }

    /**
     * Convert into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param int $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * Dump the money.
     *
     * @return array
     */
    public function __debugInfo()
    {
        return $this->toArray();
    }

    /**
     * Output as a string.
     */
    public function __toString()
    {
        return $this->format('$0,0.00');
    }
}
