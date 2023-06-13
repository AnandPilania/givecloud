<?php

namespace Tests\Unit\Domain\Commerce\Shipping\Carriers;

use Ds\Domain\Commerce\Shipping\Carriers\UPS;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\InteractsWithShipmentOptions;
use Tests\TestCase;

class UPSTest extends TestCase
{
    use InteractsWithShipmentOptions;

    public function testParsesResponseIntoRates(): void
    {
        sys_set([
            'shipping_from_state' => 'CO',
            'shipping_from_zip' => '80302',
            'shipping_from_country' => 'US',
        ]);

        $shippingOptions = $this->generateShippingOptionsForDestinationInCalifornia();

        Http::fake([
            'ups.com/*' => Http::fixture('ups/rates.xml'),
        ]);

        $rates = $this->app->make(UPS::class)->getRates($shippingOptions);

        $this->assertCount(6, $rates);
        $this->assertSame('UPS: 3 Day Select', $rates[0]->title);
    }
}
