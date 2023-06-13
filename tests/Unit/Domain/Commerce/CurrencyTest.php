<?php

namespace Tests\Unit\Domain\Commerce;

use Ds\Common\GeoIp;
use Ds\Domain\Commerce\Currency;
use Tests\TestCase;

class CurrencyTest extends TestCase
{
    /**
     * @dataProvider formattingProvider
     */
    public function testFormatting(string $locale, string $expected, $amount, string $format): void
    {
        app()->setLocale($locale);

        $this->assertSame($expected, numeral($amount, 'USD')->format($format));
    }

    public function formattingProvider(): array
    {
        return [
            ['en-US', '1,400.20', 1400.2, '0,0.00'],
            ['fr-CA', '1 400,20', 1400.2, '0,0.00'],
        ];
    }

    public function testResolvingAnIpAddressToTheBestLocalCurrency(): void
    {
        sys_set(['local_currencies' => 'USD,CAD,GBP']);

        $this->instance('geoip', $this->partialMock(GeoIp::class, function ($mock) {
            $mock->shouldReceive('getLocationData')->once()->andReturn((object) ['iso_code' => 'CA']);
        }));

        $this->assertSame('CAD', Currency::getBestLocalCurrencyForIp('174.112.241.31')->getCode());
    }

    public function testResolvingAnIpAddressesForCountryWithUnsupportedCurrencyUsesTheDefaultCurrency(): void
    {
        sys_set(['local_currencies' => 'USD,CAD,GBP']);

        $this->instance('geoip', $this->partialMock(GeoIp::class, function ($mock) {
            $mock->shouldReceive('getLocationData')->once()->andReturn((object) ['iso_code' => 'CN']);
        }));

        $this->assertSame(currency()->getCode(), Currency::getBestLocalCurrencyForIp('116.8.124.78')->getCode());
    }

    public function testResolvingAnBadIpAddressesToTheDefaultCurrency(): void
    {
        $this->assertSame(currency()->getCode(), Currency::getBestLocalCurrencyForIp('127.0.0.1')->getCode());
    }

    public function testInvalidPropertiesReturnNull(): void
    {
        $this->assertNull(currency()->this_is_not_a_real_property);
    }

    public function testUsesCodeAsStringRepresentation(): void
    {
        $this->assertSame('USD', (string) currency('USD'));
    }

    public function testStringRepresentationForJsonEncoding(): void
    {
        $this->assertSame(currency('USD')->toJson(), '"USD"');
    }
}
