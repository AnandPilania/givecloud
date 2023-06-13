<?php

namespace Ds\Domain\Webhook\Jobs;

use Ds\Domain\Webhook\Services\HookService;
use Ds\Http\Resources\TransactionResource;
use Ds\Jobs\Job;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class DeliverTransactionBatchResourceHook extends Job implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use SerializesModels;

    protected string $eventName;

    protected Collection $transactions;

    public function __construct(string $eventName, Collection $transactions)
    {
        $this->eventName = $eventName;
        $this->transactions = $transactions;
    }

    public function handle(HookService $hookService): void
    {
        $hookService->makeDeliveries($this->eventName, TransactionResource::collection($this->transactions));
    }
}
