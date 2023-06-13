<?php

namespace Tests\Unit\Common\ISO3166;

use Ds\Common\ISO3166\ISO3166;
use Tests\TestCase;

class ISO3166Test extends TestCase
{
    public function testCountriesDefaultToEnglish(): void
    {
        $this->assertArrayHasArrayWithValue($this->app->make(ISO3166::class)->countries(), $this->getCountryName());
    }

    public function testCountriesAreLocalized(): void
    {
        $this->app->setLocale('es-MX');

        $this->assertArrayHasArrayWithValue($this->app->make(ISO3166::class)->countries(), $this->getCountryName());
    }

    public function testCountryWithNumeric(): void
    {
        $codeUS = '840';
        $country = $this->app->make(ISO3166::class)->country($codeUS);

        $this->assertSame($this->getCountryName(), $country['name']);
        $this->assertSame($codeUS, $country['numeric']);
        $this->assertArrayHasKey('currency', $country);
    }

    public function testCountryWithNumericLocalized(): void
    {
        $this->app->setLocale('es-MX');

        $codeUS = '840';
        $country = $this->app->make(ISO3166::class)->country($codeUS);

        $this->assertSame($this->getCountryName(), $country['name']);
        $this->assertSame($codeUS, $country['numeric']);
        $this->assertArrayHasKey('currency', $country);
    }

    public function testCountryWithName(): void
    {
        $nameUS = $this->getCountryName();
        $country = $this->app->make(ISO3166::class)->country($nameUS);

        $this->assertSame($nameUS, $country['name']);
        $this->assertSame('US', $country['alpha_2']);
        $this->assertArrayHasKey('currency', $country);
    }

    public function testCountryWithNameLocalized(): void
    {
        $this->app->setLocale('es-MX');

        $nameUS = $this->getCountryName();
        $country = $this->app->make(ISO3166::class)->country($nameUS);

        $this->assertSame($nameUS, $country['name']);
        $this->assertSame('US', $country['alpha_2']);
        $this->assertArrayHasKey('currency', $country);
    }

    public function testCountryWithAlpha2Code(): void
    {
        $codeUS = 'US';
        $country = $this->app->make(ISO3166::class)->country($codeUS);

        $this->assertSame($this->getCountryName(), $country['name']);
        $this->assertSame($codeUS, $country['alpha_2']);
        $this->assertArrayHasKey('currency', $country);
    }

    public function testCountryWithAlpha2CodeLocalized(): void
    {
        $this->app->setLocale('es-MX');

        $codeUS = 'US';
        $country = $this->app->make(ISO3166::class)->country($codeUS);

        $this->assertSame($this->getCountryName(), $country['name']);
        $this->assertSame($codeUS, $country['alpha_2']);
        $this->assertArrayHasKey('currency', $country);
    }

    public function testCountryWithAlpha3Code(): void
    {
        $codeUS = 'USA';
        $country = $this->app->make(ISO3166::class)->country($codeUS);

        $this->assertSame($codeUS, $country['alpha_3']);
        $this->assertSame($this->getCountryName(), $country['name']);
        $this->assertArrayHasKey('currency', $country);
    }

    public function testCountryWithAlpha3CodeLocalized(): void
    {
        $this->app->setLocale('es-MX');

        $codeUS = 'USA';
        $country = $this->app->make(ISO3166::class)->country($codeUS);

        $this->assertSame($this->getCountryName(), $country['name']);
        $this->assertSame($codeUS, $country['alpha_3']);
        $this->assertArrayHasKey('currency', $country);
    }

    public function testCountryReturnsNullWhenNoMatch(): void
    {
        $this->assertNull($this->app->make(ISO3166::class)->country(''));
    }

    public function testCountryWithAlpha2CodeReturnsNameAttribute(): void
    {
        $this->assertSame(
            $this->getCountryName(),
            $this->app->make(ISO3166::class)->country('US', 'name')
        );
    }

    public function testCountryWithAlpha2CodeReturnsNameAttributeLocalized(): void
    {
        $this->app->setLocale('es-MX');

        $this->assertSame(
            $this->getCountryName(),
            $this->app->make(ISO3166::class)->country('US', 'name')
        );
    }

    public function testCountryReturnsNullWhenNoMatchAttribute(): void
    {
        $this->assertNull($this->app->make(ISO3166::class)->country('US', 'unknow-attribute'));
    }

    public function testSubdivisions(): void
    {
        $usSubdivisions = $this->app->make(ISO3166::class)->subdivisions('CA');

        $this->assertIsArray($usSubdivisions);
        $this->assertArrayHasArrayWithValue($usSubdivisions, 'British Columbia');
    }

    public function testSubdivisionsUS(): void
    {
        $usSubdivisions = $this->app->make(ISO3166::class)->subdivisions('US');

        $this->assertIsArray($usSubdivisions);
        $this->assertArrayHasArrayWithValue($usSubdivisions, 'California');
    }

    public function testSubdivisionsNoMatch(): void
    {
        $usSubdivisions = $this->app->make(ISO3166::class)->subdivisions('GC');

        $this->assertIsArray($usSubdivisions);
        $this->assertEmpty($usSubdivisions);
    }

    public function testSubdivisionByCode(): void
    {
        $californiaSubdivision = $this->app->make(ISO3166::class)->subdivision('US-CA');

        $this->assertIsArray($californiaSubdivision);
        $this->assertSame($californiaSubdivision['name'], 'California');
    }

    public function testSubdivisionByCodeReturnsNameAttribute(): void
    {
        $this->assertSame('California', $this->app->make(ISO3166::class)->subdivision('US-CA', 'name'));
    }

    public function testSubdivisionByCodeNoMatch(): void
    {
        $this->assertNull($this->app->make(ISO3166::class)->subdivision('CA-GC'));
    }

    public function testSubdivisionByCodeNoMatchReturnsNameAttribute(): void
    {
        $this->assertNull($this->app->make(ISO3166::class)->subdivision('US-GC', 'name'));
    }

    public function testSubdivisionByName(): void
    {
        $californiaSubdivision = $this->app->make(ISO3166::class)->subdivision('California');

        $this->assertIsArray($californiaSubdivision);
        $this->assertSame($californiaSubdivision['name'], 'California');
    }

    public function testExpandAbbr(): void
    {
        $californiaExpandedAddress = $this->app->make(ISO3166::class)->expandAbbr('CA, US');

        $this->assertIsString($californiaExpandedAddress);
        $this->assertSame('California, ' . $this->getCountryName(), $californiaExpandedAddress);
    }

    public function testExpandAbbrNoMatch(): void
    {
        $californiaExpandedAddress = $this->app->make(ISO3166::class)->expandAbbr('GC, US');

        $this->assertSame('GC, US', $californiaExpandedAddress);
    }

    private function getCountryName(string $countryCode = 'US'): ?string
    {
        return trans('countries')[$countryCode];
    }
}
