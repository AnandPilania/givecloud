<?php

namespace Ds\Listeners\Order;

use Ds\Domain\Shared\Exceptions\MessageException;
use Ds\Events\OrderWasCompleted;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class IssueTaxReciept implements ShouldQueue
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
        if (sys_get('tax_receipt_pdfs') && $event->order->tax_receipt_type === 'single') {
            try {
                $event->order->issueTaxReceipt(true);
            } catch (MessageException $e) {
                // ignore message exceptions
            }
        }
    }

    public function viaQueue()
    {
        return 'low';
    }
}
