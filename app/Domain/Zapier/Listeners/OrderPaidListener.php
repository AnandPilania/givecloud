<?php

namespace Ds\Domain\Zapier\Listeners;

use Ds\Domain\Zapier\Jobs\OrderPaidTrigger;
use Ds\Events\OrderWasCompleted;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class OrderPaidListener implements ShouldQueue
{
    use Queueable;

    public function handle(OrderWasCompleted $event): void
    {
        OrderPaidTrigger::dispatch($event->order);
    }

    public function viaQueue()
    {
        return 'low';
    }
}
