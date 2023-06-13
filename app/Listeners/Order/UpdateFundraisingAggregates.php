<?php

namespace Ds\Listeners\Order;

use Ds\Events\OrderWasRefunded;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateFundraisingAggregates implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(OrderWasRefunded $event): void
    {
        $event->order->items->each(fn ($item) => optional($item->fundraisingPage)->updateAggregates());
    }

    public function shouldQueue(OrderWasRefunded $event): bool
    {
        return $event->order->items->filter(fn ($item) => $item->fundraising_page_id)->isNotEmpty();
    }
}
