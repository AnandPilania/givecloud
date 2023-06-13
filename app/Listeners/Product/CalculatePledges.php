<?php

namespace Ds\Listeners\Product;

use Ds\Events\ProductWasPurchased;
use Ds\Models\Pledge;
use Illuminate\Contracts\Queue\ShouldQueue;

class CalculatePledges implements ShouldQueue
{
    public function handle(ProductWasPurchased $event): void
    {
        Pledge::calculateByMemberAndProduct($event->item->order->member, $event->product);
    }

    public function shouldQueue(ProductWasPurchased $event): bool
    {
        return feature('pledges') && isset($event->item->order->member);
    }

    public function viaQueue()
    {
        return 'low';
    }
}
