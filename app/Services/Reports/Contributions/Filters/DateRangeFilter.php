<?php

namespace Ds\Services\Reports\Contributions\Filters;

use Illuminate\Database\Eloquent\Builder;

class DateRangeFilter
{
    public function __invoke(Builder $query): Builder
    {
        if (request()->isNotFilled('fd0')) {
            return $query;
        }

        $dates = explode(' to ', request('fd0'));

        return $query->whereBetween('contribution_date', [
            fromLocal($dates[0])->startOfDay()->toUtc(),
            fromLocal($dates[1])->endOfDay()->toUtc(),
        ]);
    }
}
