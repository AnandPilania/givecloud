<?php

namespace Ds\Listeners\Order;

use Ds\Events\OrderWasCompleted;
use Ds\Events\ProductWasPurchased;

class ProductPurchases
{
    /**
     * Handle the event.
     *
     * @param \Ds\Events\OrderWasCompleted $event
     * @return void
     */
    public function handle(OrderWasCompleted $event)
    {
        foreach ($event->order->items as $item) {
            if ($item->variant && $item->variant->product) {
                event(new ProductWasPurchased($item->variant->product, $item));
            }
        }
    }
}
