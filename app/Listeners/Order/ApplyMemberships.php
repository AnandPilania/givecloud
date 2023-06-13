<?php

namespace Ds\Listeners\Order;

use Ds\Events\OrderWasCompleted;

class ApplyMemberships
{
    /**
     * Handle the event.
     *
     * @param \Ds\Events\OrderWasCompleted $event
     * @return void
     */
    public function handle(OrderWasCompleted $event)
    {
        $event->order->applyMemberships();
    }
}
