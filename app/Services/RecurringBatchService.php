<?php

namespace Ds\Services;

use Ds\Events\RecurringBatchCompleted;
use Ds\Mail\RppProcessingSummary;
use Ds\Models\RecurringBatch;
use Ds\Models\User;
use Ds\Repositories\RecurringBatchRepository;

class RecurringBatchService
{
    /** @var \Ds\Repositories\RecurringBatchRepository */
    private $recurringBatchRepository;

    public function __construct(RecurringBatchRepository $recurringBatchRepository)
    {
        $this->recurringBatchRepository = $recurringBatchRepository;
    }

    public function start(int $accountCount, int $maxSimultaneous): RecurringBatch
    {
        return RecurringBatch::create([
            'batched_on' => now(),
            'started_at' => now(),
            'max_simultaneous' => $maxSimultaneous,
            'accounts_count' => $accountCount,
        ]);
    }

    public function accountProcessed(RecurringBatch $recurringBatch): bool
    {
        if ($recurringBatch->finished_at) {
            return false;
        }

        $recurringBatch->elapsed_time = now()->diffInSeconds($recurringBatch->started_at);
        $recurringBatch->accounts_processed++;

        return $recurringBatch->save();
    }

    public function finish(RecurringBatch $recurringBatch): bool
    {
        if ($recurringBatch->finished_at) {
            return false;
        }

        $recurringBatch->finished_at = now();
        $recurringBatch->elapsed_time = now()->diffInSeconds($recurringBatch->started_at);

        $data = $this->recurringBatchRepository->getTransactionAggregates($recurringBatch);

        $recurringBatch->transactions_approved = $data->transactions_approved;
        $recurringBatch->transactions_declined = $data->transactions_declined;

        $saved = $recurringBatch->save();

        RecurringBatchCompleted::dispatch($recurringBatch);

        return $saved;
    }

    public function sendSummaryToAccountAdmins(RecurringBatch $recurringBatch): void
    {
        if ($recurringBatch->finished_at) {
            User::mailAccountAdmins(new RppProcessingSummary($recurringBatch), function (User $user) {
                return $user->notify_recurring_batch_summary === true;
            });
        }
    }
}
