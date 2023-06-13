<?php

namespace Tests\Feature\Backend;

use Ds\Models\Order;
use Ds\Models\OrderItem;
use Ds\Models\Payment;
use Ds\Models\Product;
use Ds\Models\Variant;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    public function testReturnsContributions(): void
    {
        Order::factory(4)->create(['confirmationdatetime' => now()]);

        $this->actingAsUser()
            ->withUserPermissions('order.view')
            ->post(route('backend.orders.listing'))
            ->assertJsonPath('recordsTotal', 4);
    }

    /**
     * @dataProvider filteringOrderSourceProvider
     */
    public function testFilteringByPointOfSale(int $expecting, array $sources)
    {
        Order::factory(4)->create(['confirmationdatetime' => now()]);
        Order::factory(8)->pointOfSale()->create(['confirmationdatetime' => now()]);

        Order::factory(10)->pointOfSale()->create([
            'confirmationdatetime' => now(),
            'source' => 'Phone',
        ]);

        $res = $this->actingAsUser()
            ->withUserPermissions('order.view')
            ->post(route('backend.orders.listing'), ['fs' => $sources]);

        $res->assertJsonPath('recordsTotal', $expecting);
    }

    public function filteringOrderSourceProvider(): array
    {
        return [
            [18, ['Point of Sale (POS)']],
            [10, ['Point of Sale (POS)', 'Phone']],
        ];
    }

    public function testCanFilterOnDonationForms(): void
    {
        Order::factory(4)->create(['confirmationdatetime' => now()]);

        $product = Product::factory()->donationForm()->create();

        Order::factory()->has(
            OrderItem::factory()->for(
                Variant::factory()->for($product)
            ),
            'items'
        )->create(['confirmationdatetime' => now()]);

        $this->actingAsUser()
            ->withUserPermissions('order.view')
            ->post(route('backend.orders.listing'), ['df' => $product->hashid])
            ->assertJsonPath('recordsTotal', 1);
    }

    /** @dataProvider riskWarningDataProvider */
    public function testCanFilterOnRiskWarning(string $risk): void
    {
        Order::factory(4)->completed()->paid()->create(['confirmationdatetime' => now()]);

        Order::factory()->completed()->paid()
            ->has(
                Payment::factory(1)
                    ->paid()
                    ->card()
                    ->state([$risk => 'fail'])
            )->create();

        $this->actingAsUser()
            ->withUserPermissions('order.view')
            ->post(route('backend.orders.listing'), ['c' => 3])
            ->assertJsonPath('recordsTotal', 1);
    }

    public function riskWarningDataProvider(): array
    {
        return [
            ['card_cvc_check'],
            ['card_address_line1_check'],
            ['card_address_zip_check'],
        ];
    }

    public function testCanFilterOnOkNoWarnings(): void
    {
        Order::factory(4)->completed()->paid()->has(
            Payment::factory()->paid()
        )->create(['confirmationdatetime' => now()]);

        Order::factory()->completed()->paid()->has(
            Payment::factory()->paid()
        )->create([
            'ip_country' => 'CA',
            'billingcountry' => 'US',
        ]);

        Order::factory()
            ->completed()
            ->paid()
            ->has(
                Payment::factory()
                    ->paid()
                    ->card()
                    ->state(['card_cvc_check' => 'fail'])
            )->create();

        $r = $this->actingAsUser()
            ->withUserPermissions('order.view')
            ->post(route('backend.orders.listing'), ['c' => 4]);

        $r->assertJsonPath('recordsTotal', 4);
    }
}
