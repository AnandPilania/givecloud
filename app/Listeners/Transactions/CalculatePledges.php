<?php

namespace Ds\Listeners\Transactions;

use Ds\Events\RecurringPaymentEventInterface;
use Ds\Models\Pledge;
use Ds\Models\Product;
use Illuminate\Contracts\Queue\ShouldQueue;

class CalculatePledges implements ShouldQueue
{
    public function handle(RecurringPaymentEventInterface $event): void
    {
        Pledge::calculateByMemberAndProduct($event->rpp->member, $event->rpp->product);
    }

    public function shouldQueue(RecurringPaymentEventInterface $event): bool
    {
        return feature('pledges') && $event->rpp->product && $this->productHasPledges($event->rpp->product);
    }

    public function viaQueue()
    {
        return 'low';
    }

    private function productHasPledges(Product $product): bool
    {
        return $product->pledgeCampaigns()->has('pledges')->exists();
    }
}
