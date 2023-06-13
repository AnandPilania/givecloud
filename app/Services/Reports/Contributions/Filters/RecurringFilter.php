<?php

namespace Ds\Services\Reports\Contributions\Filters;

use Illuminate\Database\Eloquent\Builder;

class RecurringFilter
{
    public function __invoke(Builder $query): Builder
    {
        if (request()->isNotFilled('recurring')) {
            return $query;
        }

        return $query->where('is_recurring', request('recurring') === '1');
    }
}
