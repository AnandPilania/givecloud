<?php

namespace Tests\Unit\Services;

use Ds\Services\GeoIpService;
use GeoIp2\Exception\AddressNotFoundException;
use Tests\TestCase;

class GeoIpServiceTest extends TestCase
{
    /** @dataProvider geoKeysDataProvioder */
    public function testGetReturnsValueForGivenKey(string $key, string $expected): void
    {
        $mock = $this->partialMock(GeoIpService::class);

        $mock->shouldReceive('getLocationData')
            ->once()
            ->andReturn((object) [
                'iso_code' => 'US',
                'country' => 'United States',
                'timezone' => 'America/Chicago',
                'continent' => 'NA',
            ]);

        $value = $mock->get($key, '8.8.8.8');

        $this->assertSame($expected, $value);
    }

    public function geoKeysDataProvioder(): array
    {
        return [
            ['iso_code', 'US'],
            ['country', 'United States'],
            ['timezone', 'America/Chicago'],
            ['continent', 'NA'],
        ];
    }

    public function testGetDoesNotThrowExceptionsForInvalidIps(): void
    {
        $mock = $this->partialMock(GeoIpService::class);
        $mock->shouldReceive('getLocationData')->andThrow(new AddressNotFoundException);

        $value = $mock->get('timezone', '0.0.0.0');

        $this->assertNull($value);
    }

    public function testGetReturnsDefaultValueForInvalidKeysAndInvalidIps(): void
    {
        $mock = $this->partialMock(GeoIpService::class);
        $mock->shouldReceive('getLocationData')->andThrow(new AddressNotFoundException);

        $value = $mock->get('timezone', 'invalid.ip.0.0', 'my default value');
        $this->assertSame('my default value', $value);

        $value = $mock->get('inexisting_key', '8.8.8.8', 'some other default value');
        $this->assertSame('some other default value', $value);
    }
}
