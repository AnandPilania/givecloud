<?php

namespace Tests\Feature\Console\Commands;

use Ds\Models\Order;
use Ds\Models\OrderItem;
use Ds\Models\Payment;
use Ds\Models\Product;
use Ds\Models\Variant;
use Tests\TestCase;

class CleanupOrderTableCommandTest extends TestCase
{
    public function testCommandCanCleanNoOrders(): void
    {
        $this->assertArtisanOutput();
    }

    public function testCommandCanPruneEmptyCarts(): void
    {
        // Create recent empty carts
        Order::factory(3)->create();

        // Create expired empty cart
        $order = Order::factory()->create(['started_at' => toUtcFormat('-120 days')]);

        $this->assertArtisanOutput(1);

        $this->assertDatabaseMissing('productorder', ['id' => $order->id]);
    }

    public function testDoesNotPruneNotEmptyCarts(): void
    {
        // Create expired cart w/product
        $order = $this->createOrderWithItem(['started_at' => toUtcFormat('-120 days')]);

        $this->assertArtisanOutput();

        $this->assertDatabaseHas('productorder', ['id' => $order->id]);
    }

    public function testCommandCanPruneAbandonedCarts(): void
    {
        // Create expired cart w/product
        $order = $this->createOrderWithItem(['started_at' => toUtcFormat('-3 years')]);

        $this->assertArtisanOutput(0, 1);

        $this->assertDatabaseMissing('productorder', ['id' => $order->id]);
    }

    public function testCommandDoesNotPruneRecentlyAbandonedCarts(): void
    {
        // Create expired cart w/product
        $order = $this->createOrderWithItem(['started_at' => toUtcFormat('-1 years')]);

        $this->assertArtisanOutput();

        $this->assertDatabaseHas('productorder', ['id' => $order->id]);
    }

    public function testCommandDoesNotPruneAbandonedCheckouts(): void
    {
        $order = $this->createOrderWithItem();
        $order->payments()->save(Payment::factory()->create());

        $this->assertArtisanOutput();

        $this->assertDatabaseHas('productorder', ['id' => $order->id]);
    }

    protected function assertArtisanOutput(int $emptyCarts = 0, int $abandonedCarts = 0): void
    {
        $this->artisan('cleanup:ordertable')
            ->expectsOutput("Pruned $emptyCarts empty carts (over 90 days)")
            ->expectsOutput("Pruned $abandonedCarts abandoned carts (over 2 years)")
            ->assertExitCode(0);
    }

    protected function createOrderWithItem(array $attributes = []): Order
    {
        return OrderItem::factory()
            ->for(Order::factory()->state($attributes))
            ->for(Variant::factory()->for(Product::factory()))
            ->create()->order;
    }
}
