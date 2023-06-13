<?php

namespace Ds\Listeners\Order;

use Ds\Events\OrderWasCompleted;

class IncrementPromoCodes
{
    /**
     * Handle the event.
     *
     * @param \Ds\Events\OrderWasCompleted $event
     * @return void
     */
    public function handle(OrderWasCompleted $event)
    {
        // for every promocode referenced on the order
        $event->order->promoCodes->each(function ($promo) {
            // increment each promo's usage_count
            $promo->incrementUsageCount();
        });
    }
}
