<?php

namespace Tests\Concerns;

use Ds\Domain\Commerce\Shipping\ShipmentOptions;
use Ds\Models\Order;

trait InteractsWithShipmentOptions
{
    private function generateShippingOptionsForDestinationInCalifornia(): ShipmentOptions
    {
        return new ShipmentOptions(Order::factory()->make([
            'client_uuid' => '19E2B09EC0',
            'shipname' => 'Jaine Doe',
            'shipaddress1' => '1600 Amphitheatre Parkway',
            'shipcity' => 'Mountain View',
            'shipstate' => 'CA',
            'shipzip' => '94043',
            'shipcountry' => 'US',
            'total_weight' => 0.25,
        ]));
    }

    private function generateShippingOptionsForDestinationInOntario(): ShipmentOptions
    {
        return new ShipmentOptions(Order::factory()->make([
            'client_uuid' => '19E2B09EC0',
            'shipname' => 'Givecloud Inc',
            'shipaddress1' => '116 Albert Street',
            'shipaddress2' => 'Suite 300',
            'shipcity' => 'Ottawa',
            'shipstate' => 'ON',
            'shipzip' => 'K1P 5E3',
            'shipcountry' => 'CA',
            'total_weight' => 0.25,
        ]));
    }
}
