<?php

namespace Ds\Services\Reports\PaymentsDetails\Filters;

use Ds\Models\Transaction;
use Illuminate\Database\Eloquent\Builder;

class RecurringFilter
{
    public function __invoke(Builder $query): Builder
    {
        if (request()->isNotFilled('recurring')) {
            return $query;
        }

        if (request('recurring') === 'onetime') {
            return $query->where('ledgerable_type', 'order');
        }

        if (request('recurring') === 'recurring') {
            return $query->where('ledgerable_type', Transaction::class);
        }

        return $query;
    }
}
