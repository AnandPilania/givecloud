<?php

namespace Ds\Services\Reports\Contributions\Filters;

use Illuminate\Database\Eloquent\Builder;

class ContributionTypeFilter
{
    public function __invoke(Builder $query): Builder
    {
        if (request()->isNotFilled('fct')) {
            return $query;
        }

        if (request('fct-initial')) {
            return $query->where(function (Builder $query) {
                $query->where('recurring_items', '>', 0)
                    ->orWhere('is_recurring', request('fct') === '2');
            });
        }

        return $query->where('is_recurring', request('fct') === '2');
    }
}
