<?php

namespace Ds\Listeners\OrderItem;

use Ds\Events\ProductWasPurchased;

class UpdateFundraisingAggregates
{
    /**
     * Handle the event.
     *
     * @param \Ds\Events\ProductWasPurchased $event
     * @return void
     */
    public function handle(ProductWasPurchased $event)
    {
        if ($event->item->fundraising_page_id) {
            $event->item->fundraisingPage->updateAggregates();
        }
    }
}
