<?php

namespace Ds\Events;

use Ds\Models\Order;
use Illuminate\Queue\SerializesModels;

class OrderWasRefunded extends Event
{
    use SerializesModels;

    /** @var \Ds\Models\Order */
    public $order;

    /**
     * Create a new event instance.
     *
     * @param \Ds\Models\Order $order
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order;

        // fire events for each product that was purchased
        foreach ($order->items as $item) {
            if ($item->variant && $item->variant->product) {
                event(new ProductWasRefunded($item->variant->product, $item));
            }
        }
    }
}
