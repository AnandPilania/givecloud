<?php

namespace Tests\Unit\Domain\Zapier\Jobs;

use Ds\Domain\Zapier\Enums\Events;
use Ds\Domain\Zapier\Jobs\OrderPaidTrigger;
use Ds\Models\Order;
use Ds\Models\User;

/**
 * @group zapier
 */
class OrderTriggerTest extends AbstractTriggers
{
    public function testHandle(): void
    {
        $user = $this->createUserWithAccountAndSubs(Events::CONTRIBUTION_PAID);

        $this->mockAndcallTrigger(OrderPaidTrigger::class, $user->resthookSubscriptions, $this->createOrder($user));
    }

    public function testHandleMultipleHooks(): void
    {
        $user = $this->createUserWithAccountAndSubs(Events::CONTRIBUTION_PAID, 3);

        $order = Order::factory()->create();
        $user->members->first()->orders()->saveMany([$order]);
        $order->afterProcessed();

        $this->mockAndcallTrigger(OrderPaidTrigger::class, $user->resthookSubscriptions, $this->createOrder($user));
    }

    protected function createOrder(User $user): Order
    {
        $order = Order::factory()->create();
        $user->members->first()->orders()->saveMany([$order]);
        $order->afterProcessed();

        return $order;
    }
}
