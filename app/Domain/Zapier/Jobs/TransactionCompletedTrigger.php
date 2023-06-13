<?php

namespace Ds\Domain\Zapier\Jobs;

use Ds\Domain\Zapier\Enums\Events;
use Ds\Http\Resources\TransactionResource;
use Ds\Models\Transaction;
use Ds\Repositories\ResthookSubscriptionRepository;

class TransactionCompletedTrigger extends ZapierAbstractTrigger
{
    protected Transaction $transaction;

    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
    }

    public function handle(ResthookSubscriptionRepository $resthookSubscriptionRepository): void
    {
        $this->pushToZapier(Events::CONTRIBUTION_PAID, TransactionResource::collection([$this->transaction]), $resthookSubscriptionRepository);
    }
}
