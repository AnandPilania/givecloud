<?php

namespace Tests\Unit\Domain\QuickStart\Listeners;

use Ds\Domain\QuickStart\Listeners\PaymentOccurredListener;
use Ds\Domain\QuickStart\QuickStartService;
use Ds\Events\OrderWasCompleted;
use Ds\Models\Order;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/** @group QuickStart */
class PaymentOccurredListenerTest extends TestCase
{
    public function testListenerIsAttached(): void
    {
        Event::fake();
        Event::assertListening(
            OrderWasCompleted::class,
            PaymentOccurredListener::class
        );
    }

    public function testUpsertsSupporterWhenAccountIsUpdated(): void
    {
        $this->mock(QuickStartService::class)->shouldReceive('updateTaskStatus')->once();

        $event = new OrderWasCompleted(Order::factory()->create());
        $this->app->make(PaymentOccurredListener::class)->handle($event);
    }
}
