<?php

namespace Ds\Listeners\Order;

use Ds\Domain\Commerce\Support\TaxCloud\TaxCloudRepository;
use Ds\Events\OrderWasCompleted;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class TaxCloudCapture implements ShouldQueue
{
    use Queueable;

    /**
     * Handle the event.
     *
     * @param \Ds\Events\OrderWasCompleted $event
     * @return void
     */
    public function handle(OrderWasCompleted $event)
    {
        // don't do taxcloud if TaxCloud is not connected
        if (sys_get('taxcloud_api_key')) {
            app(TaxCloudRepository::class)->capture($event->order);
        }
    }

    public function viaQueue()
    {
        return 'low';
    }
}
