<?php

namespace Ds\Services\Reports\Contributions\Filters;

use Illuminate\Database\Eloquent\Builder;

class SupporterTypeFilter
{
    public function __invoke(Builder $query): Builder
    {
        if (request()->isNotFilled('fat')) {
            return $query;
        }

        return $query->whereIn('member.account_type_id', request('fat'));
    }
}
