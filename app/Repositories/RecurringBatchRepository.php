<?php

namespace Ds\Repositories;

use Ds\Models\RecurringBatch;
use Illuminate\Support\Facades\DB;

class RecurringBatchRepository
{
    public function getTransactionAggregates(RecurringBatch $recurringBatch): \stdClass
    {
        return $recurringBatch->transactions()
            ->select([
                DB::raw("sum(if(payment_status = 'Completed', 1, 0)) as transactions_approved"),
                DB::raw("sum(if(payment_status = 'Completed', 0, 1)) as transactions_declined"),
            ])->toBase()
            ->first();
    }
}
