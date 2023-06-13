<?php

namespace Ds\Listeners\Transactions;

use Ds\Events\RecurringPaymentWasRefunded;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateFundraisingAggregates implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(RecurringPaymentWasRefunded $event): void
    {
        optional($event->rpp->order_item->fundraisingPage ?? null)->updateAggregates();
    }

    public function shouldQueue(RecurringPaymentWasRefunded $event): bool
    {
        return (bool) $event->rpp->order_item->fundraising_page_id;
    }
}
