<?php

namespace Tests\Unit\Domain\Commerce;

use Ds\Domain\Commerce\Money;
use Money\Money as PhpMoney;
use Tests\TestCase;

class MoneyTest extends TestCase
{
    /**
     * @dataProvider formattingProvider
     */
    public function testFormatting(string $expected, $amount, string $format, string $currency = null, array $conditions = []): void
    {
        $this->assertSame($expected, money($amount, $currency)->format($format, $conditions));
    }

    public function formattingProvider(): array
    {
        return [
            ['$1,400', 1400.2, '0'],
            ['$1,400.2', 1400.2, '1'],
            ['$1,400.20', 1400.2, '2'],
            ['£1,400.20', 1400.2, '2', 'GBP'],
            ['$1,400.20 USD', 1400.2, '$0,0.00 $$$', 'USD'],
            ['$1,400.20 CAD', 1400.2, '$0,0.00 $$$', 'CAD'],
            ['£1,400.20 GBP', 1400.2, '$0,0.00 $$$', 'GBP'],
            ['£1,400.20', 1400.2, '$0,0.00 [$$$]', 'GBP'],
            ['$130', 130, '$0[.]0a'],
            ['$130.65', 130.65, '$0.0a'],
            ['$1.3k', 1300.65, '$0.0a'],
            ['$1.3k', 1300.65, '$0.0a', 'USD', ['$0.0' => false, '$0.00' => false]],
            ['$1300.65', 1300.65, '$0.0a', 'USD', ['$0.0' => false, '$0.00' => true]],
            ['$1300.7', 1300.65, '$0.0a', 'USD', ['$0.0' => true, '$0.00' => false]],
        ];
    }

    public function testSwitchingCurrencies(): void
    {
        $amountInCad = money(new PhpMoney(1000, currency('CAD')->toPhpCurrency()));
        $amountInUsd = money($amountInCad, 'USD');

        $this->assertSame('USD', $amountInUsd->currency_code);
    }

    public function testSwitchingCurrenciesToTheSameCurrency(): void
    {
        $amountInDefaultCurrency = money(10);

        $this->assertSame(
            $amountInDefaultCurrency->currency_code,
            $amountInDefaultCurrency->toDefaultCurrency()->currency_code,
        );
    }

    public function testInvalidPropertiesReturnNull(): void
    {
        $this->assertNull(money(10)->this_is_not_a_real_property);
    }

    public function testUsesCurrencyFormatAsDefault(): void
    {
        $this->assertSame('$1,000.00', (string) money(1000));
    }

    public function testDebugInfoMatchesToArray(): void
    {
        $money = money(10);

        $this->assertSame($money->toArray(), $money->__debugInfo());
    }

    public function testJsonEncoding(): void
    {
        $this->assertSame(money(10)->toJson(), '{"amount":10,"currency_code":"USD"}');
    }

    /**
     * @dataProvider amountInSubunitsValueProvider
     */
    public function testGetAmountInSubunitsValues(string $currencyCode, $amount, int $expectedAmountInSubunits): void
    {
        $price = new Money($amount, $currencyCode);

        $this->assertSame($expectedAmountInSubunits, $price->getAmountInSubunits());
    }

    public function amountInSubunitsValueProvider(): array
    {
        return [
            ['USD', 25, 2500],
            ['USD', 25.50, 2550],
            ['VND', 250, 250],
            ['VND', 250.50, 250],
        ];
    }
}
