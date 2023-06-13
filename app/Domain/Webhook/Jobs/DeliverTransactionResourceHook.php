<?php

namespace Ds\Domain\Webhook\Jobs;

use Ds\Domain\Webhook\Services\HookService;
use Ds\Http\Resources\TransactionResource;
use Ds\Jobs\Job;
use Ds\Models\Transaction;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeliverTransactionResourceHook extends Job implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use SerializesModels;

    protected string $eventName;

    protected Transaction $transaction;

    public function __construct(string $eventName, Transaction $transaction)
    {
        $this->eventName = $eventName;
        $this->transaction = $transaction;
    }

    public function handle(HookService $hookService): void
    {
        $hookService->makeDeliveries($this->eventName, new TransactionResource($this->transaction));
    }
}
