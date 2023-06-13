<?php

namespace Ds\Listeners\Order;

use Ds\Events\OrderWasCompleted;

class CreateSponsors
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
            // if its not a sponsorship, slip to the next record
            if (is_numeric($item->sponsorship_id) && $item->sponsorship_id > 0) {
                // sponsorships require user accounts if there is no member associated with
                // this order then attempt to create a member
                if (! $event->order->member_id) {
                    $event->order->createMember();
                }

                $item->createSponsor('Website', $event->order->is_pos);
            }
        }
    }
}
