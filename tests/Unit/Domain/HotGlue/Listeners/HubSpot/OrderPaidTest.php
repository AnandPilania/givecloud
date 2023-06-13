<?php

namespace Tests\Unit\Domain\HotGlue\Listeners\HubSpot;

use Ds\Domain\HotGlue\Listeners\HubSpot\OrderPaid;
use Ds\Events\OrderWasCompleted;
use Ds\Models\Order;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * @group hotglue
 */
class OrderPaidTest extends TestCase
{
    public function testHandleSendsPayload(): void
    {
        Http::fake();

        $order = Order::factory()->paid()->create();
        $event = new OrderWasCompleted($order);

        $this->app->make(OrderPaid::class)->handle($event);

        Http::assertSent(function (Request $request) {
            return
                $request['tap'] === 'api' &&
                data_get($request, 'state.Deals.0.status') === 'closedwon';
        });
    }
}
