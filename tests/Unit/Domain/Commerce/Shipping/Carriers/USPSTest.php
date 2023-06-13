<?php

namespace Tests\Unit\Domain\Commerce\Shipping\Carriers;

use Ds\Domain\Commerce\Shipping\Carriers\USPS;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\InteractsWithShipmentOptions;
use Tests\TestCase;

class USPSTest extends TestCase
{
    use InteractsWithShipmentOptions;

    public function testParsesDomesticResponseIntoRates(): void
    {
        sys_set([
            'shipping_from_state' => 'CO',
            'shipping_from_zip' => '80302',
            'shipping_from_country' => 'US',
        ]);

        $shippingOptions = $this->generateShippingOptionsForDestinationInCalifornia();

        Http::fake([
            'shippingapis.com/*' => Http::fixture('usps/domestic-rates.xml'),
        ]);

        $rates = $this->app->make(USPS::class)->getRates($shippingOptions);

        $this->assertCount(5, $rates);
        $this->assertSame('USPS: Priority Mail Express', $rates[0]->title);
    }

    public function testParsesInternationalResponseIntoRates(): void
    {
        sys_set([
            'shipping_from_state' => 'CO',
            'shipping_from_zip' => '80302',
            'shipping_from_country' => 'US',
        ]);

        $shippingOptions = $this->generateShippingOptionsForDestinationInOntario();

        Http::fake([
            'shippingapis.com/*' => Http::fixture('usps/international-rates.xml'),
        ]);

        $rates = $this->app->make(USPS::class)->getRates($shippingOptions);

        $this->assertCount(9, $rates);
        $this->assertSame('USPS: USPS GXG Envelopes', $rates[0]->title);
    }
}
