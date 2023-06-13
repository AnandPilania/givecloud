<?php

namespace Ds\Domain\Zapier\Jobs;

use Ds\Domain\Zapier\Enums\Events;
use Ds\Http\Resources\TransactionResource;
use Ds\Repositories\ResthookSubscriptionRepository;
use Illuminate\Support\Collection;

class TransactionBatchCompletedTrigger extends ZapierAbstractTrigger
{
    protected Collection $transactions;

    public function __construct(Collection $transactions)
    {
        $this->transactions = $transactions;
    }

    public function handle(ResthookSubscriptionRepository $resthookSubscriptionRepository): void
    {
        $this->pushToZapier(Events::CONTRIBUTION_PAID, TransactionResource::collection($this->transactions), $resthookSubscriptionRepository);
    }
}
