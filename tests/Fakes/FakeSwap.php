<?php

namespace Tests\Fakes;

use DateTimeInterface;
use Exchanger\Contract\ExchangeRate as ExchangeRateContract;
use Exchanger\CurrencyPair;
use Exchanger\ExchangeRate;
use Swap\Swap;

class FakeSwap extends Swap
{
    public function __construct()
    {
        // do nothing
    }

    public function latest(string $currencyPair, array $options = []): ExchangeRateContract
    {
        return $this->quote($currencyPair, null, $options);
    }

    public function historical(string $currencyPair, DateTimeInterface $date, array $options = []): ExchangeRateContract
    {
        return $this->quote($currencyPair, $date, $options);
    }

    private function quote(string $currencyPair, DateTimeInterface $date = null, array $options = []): ExchangeRateContract
    {
        $currencyPair = CurrencyPair::createFromString($currencyPair);

        if ($currencyPair->isIdentical()) {
            $rate = 1.0;
        } else {
            // random value between 0.6 and 1.3 to 4 decimal places
            $rate = round(0.6 + mt_rand() / mt_getrandmax() * (1.3 - 0.6), 4);
        }

        return new ExchangeRate($currencyPair, $rate, $date ?? now(), 'fake');
    }
}
