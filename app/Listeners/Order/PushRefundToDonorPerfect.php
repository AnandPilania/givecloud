<?php

namespace Ds\Listeners\Order;

use Ds\Events\OrderWasRefunded;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class PushRefundToDonorPerfect implements ShouldQueue
{
    use Queueable;

    /**
     * Handle the event.
     *
     * @param \Ds\Events\OrderWasRefunded $event
     * @return void
     */
    public function handle(OrderWasRefunded $event)
    {
        if (! dpo_is_enabled()) {
            return;
        }

        // if there is DP data
        if (sys_get('dp_push_order_refunds') && $event->order->dp_sync_order && $event->order->alt_transaction_id) {
            // if it was a full refund
            if ($event->order->refunded_amt == $event->order->totalamount) {
                // adjust all gifts on the order
                app('Ds\Services\DonorPerfectService')->pushOrderFullRefund($event->order);
            }

            // message from Poppy: i love you! i am so so so lucy to have you has my dad:D love poppy bloomfield to daddy bloomfield/josh bloomfield:D LOL!
        }
    }

    public function viaQueue()
    {
        return 'low';
    }
}
