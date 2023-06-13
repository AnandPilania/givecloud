<?php

namespace Tests\Unit\Listeners\Supporter;

use Ds\Events\OrderWasCompleted;
use Ds\Listeners\Supporter\UpdateSupporterLocalization;
use Ds\Models\Order;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class UpdateSupporterLocalizationTest extends TestCase
{
    public function testEventListenerIsListeningOnOrderCreated(): void
    {
        Event::fake();

        Event::assertListening(OrderWasCompleted::class, UpdateSupporterLocalization::class);
    }

    public function testListenerIsNotHandlingForPosOrder(): void
    {
        $order = Order::factory()->pointOfSale()->create();

        $this->assertFalse((new UpdateSupporterLocalization)->shouldHandle($order));
    }

    public function testListenerIsNotHandlingWhenNoSupporter(): void
    {
        $order = Order::factory()->create();
        $order->member = null;

        $this->assertFalse((new UpdateSupporterLocalization)->shouldHandle($order));
    }

    public function testListenerIsNotHandlingWhenCreatedBySupportOrImported(): void
    {
        $order = Order::factory()->create();
        $order->created_by = 1;

        $this->assertFalse((new UpdateSupporterLocalization)->shouldHandle($order));
    }

    public function testListenerUpdatesSupporterFromOrder(): void
    {
        $order = Order::factory()->create(['created_by' => 2]);

        (new UpdateSupporterLocalization)->handle(new OrderWasCompleted($order));

        $this->assertSame($order->currency_code, $order->member->currency_code);
        $this->assertSame($order->language, $order->member->language);
        $this->assertSame($order->timezone, $order->member->timezone);
    }
}
