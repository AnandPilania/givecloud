<?php

namespace Ds\Domain\Zapier\Listeners;

use Ds\Domain\Zapier\Jobs\TransactionCompletedTrigger;
use Ds\Events\RecurringPaymentWasCompleted as RecurringPaymentWasCompletedEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class RecurringPaymentWasCompleted implements ShouldQueue
{
    use Queueable;

    public function handle(RecurringPaymentWasCompletedEvent $event): void
    {
        // This is part of a batch, will send batch altogether.
        if ($event->transaction->recurring_batch_id) {
            return;
        }

        TransactionCompletedTrigger::dispatch($event->transaction);
    }

    public function viaQueue()
    {
        return 'low';
    }
}
