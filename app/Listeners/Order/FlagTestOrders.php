<?php

namespace Ds\Listeners\Order;

use Ds\Events\OrderWasCompleted;

class FlagTestOrders
{
    /**
     * Handle the event.
     *
     * @param \Ds\Events\OrderWasCompleted $event
     * @return void
     */
    public function handle(OrderWasCompleted $event)
    {
        if ($event->order->paymentProvider) {
            $event->order->is_test = $event->order->paymentProvider->test_mode;
            $event->order->save();
        }
    }
}
