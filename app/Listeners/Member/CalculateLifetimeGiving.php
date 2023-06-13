<?php

namespace Ds\Listeners\Member;

use Ds\Events\OrderWasCompleted;
use Ds\Events\RecurringPaymentWasCompleted;
use Ds\Jobs\CalculateLifetimeMemberGiving;

class CalculateLifetimeGiving
{
    /**
     * Handle the event.
     *
     * @param object $event
     * @return void
     */
    public function handle($event)
    {
        if ($event instanceof RecurringPaymentWasCompleted) {
            CalculateLifetimeMemberGiving::dispatch($event->rpp->member);
        } elseif ($event instanceof OrderWasCompleted) {
            if ($event->order->member) {
                CalculateLifetimeMemberGiving::dispatch($event->order->member);
            }

            foreach ($event->order->items as $item) {
                if ($item->fundraisingPage && $item->fundraisingPage->memberOrganizer) {
                    CalculateLifetimeMemberGiving::dispatch($item->fundraisingPage->memberOrganizer);
                }
            }
        }
    }
}
