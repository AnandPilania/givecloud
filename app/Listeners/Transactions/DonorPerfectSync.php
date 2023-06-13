<?php

namespace Ds\Listeners\Transactions;

use Ds\Events\RecurringPaymentWasCompleted;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DonorPerfectSync implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;

    public int $tries = 6;

    // Number of seconds before retrying the job
    public function backoff(): array
    {
        return [500, 1000, 1500, 2000];
    }

    public function handle(RecurringPaymentWasCompleted $event): void
    {
        if (! $event->transaction->commitToDpo()) {
            throw new \Exception('DP Sync failed');
        }
    }

    public function shouldQueue(RecurringPaymentWasCompleted $event): bool
    {
        if (dpo_is_enabled() && $event->transaction->dp_auto_sync) {
            return true;
        }

        $event->transaction->transactionLog('DP auto sync is turned off');

        return false;
    }

    public function viaQueue()
    {
        return 'low';
    }
}
