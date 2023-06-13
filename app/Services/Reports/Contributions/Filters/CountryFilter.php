<?php

namespace Ds\Services\Reports\Contributions\Filters;

use Illuminate\Database\Eloquent\Builder;

class CountryFilter
{
    public function __invoke(Builder $query): Builder
    {
        if (request()->isNotFilled('fc')) {
            return $query;
        }

        return $query->where('billing_country', request('fc'));
    }
}
