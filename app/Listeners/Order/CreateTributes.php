<?php

namespace Ds\Listeners\Order;

use Ds\Events\OrderWasCompleted;
use Ds\Models\Tribute;

class CreateTributes
{
    /**
     * Handle the event.
     *
     * @param \Ds\Events\OrderWasCompleted $event
     * @return void
     */
    public function handle(OrderWasCompleted $event)
    {
        Tribute::createFromOrder($event->order);
    }
}
