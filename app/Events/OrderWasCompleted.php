<?php

namespace Ds\Events;

use Ds\Models\Order;
use Illuminate\Queue\SerializesModels;

class OrderWasCompleted extends Event
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
    }
}
