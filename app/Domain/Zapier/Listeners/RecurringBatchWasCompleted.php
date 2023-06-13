<?php

namespace Ds\Domain\Zapier\Listeners;

use Ds\Domain\Zapier\Jobs\TransactionBatchCompletedTrigger;
use Ds\Events\RecurringBatchCompleted;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class RecurringBatchWasCompleted implements ShouldQueue
{
    use Queueable;

    public function handle(RecurringBatchCompleted $event): void
    {
        TransactionBatchCompletedTrigger::dispatch($event->batch->transactions);
    }

    public function viaQueue()
    {
        return 'low';
    }
}
