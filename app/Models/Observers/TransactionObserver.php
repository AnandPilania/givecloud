<?php

namespace Ds\Models\Observers;

use Ds\Models\Transaction;

class TransactionObserver
{
    public function saved(Transaction $model): void
    {
        $model->createOrUpdateContribution();
    }
}
