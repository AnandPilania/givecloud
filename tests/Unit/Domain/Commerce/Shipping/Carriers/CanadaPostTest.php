<?php

namespace Tests\Unit\Domain\Commerce\Shipping\Carriers;

use Ds\Common\Exceptionist\Manager;
use Ds\Domain\Commerce\Shipping\Carriers\CanadaPost;
use Illuminate\Support\Facades\Http;
use Mockery;
use Tests\Concerns\InteractsWithShipmentOptions;
use Tests\TestCase;

class CanadaPostTest extends TestCase
{
    use InteractsWithShipmentOptions;

    public function testHandlesApiErrors(): void
    {
        sys_set([
            'shipping_from_state' => 'ON',
            'shipping_from_zip' => 'K2E 6T7',
            'shipping_from_country' => 'CA',
        ]);

        $shippingOptions = $this->generateShippingOptionsForDestinationInOntario();

        Http::fake([
            'canadapost.ca/*' => Http::fixture('canadapost/authentication-failure.xml'),
        ]);

        $this->instance('exceptionist', Mockery::mock(Manager::class, function ($mock) {
            $mock->shouldReceive('notifyError')->once();
        })->makePartial());

        $rates = $this->app->make(CanadaPost::class)->getRates($shippingOptions);

        $this->assertCount(0, $rates);
    }

    public function testParsesDomesticResponseIntoRates(): void
    {
        sys_set([
            'shipping_from_state' => 'ON',
            'shipping_from_zip' => 'K2E 6T7',
            'shipping_from_country' => 'CA',
        ]);

        $shippingOptions = $this->generateShippingOptionsForDestinationInOntario();

        Http::fake([
            'canadapost.ca/*' => Http::fixture('canadapost/rates.xml'),
        ]);

        $rates = $this->app->make(CanadaPost::class)->getRates($shippingOptions);

        $this->assertCount(4, $rates);
        $this->assertSame('Canada Post: Expedited Parcel', $rates[0]->title);
    }
}
