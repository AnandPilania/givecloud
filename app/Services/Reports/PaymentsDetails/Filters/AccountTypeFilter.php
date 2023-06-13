<?php

namespace Ds\Services\Reports\PaymentsDetails\Filters;

use Illuminate\Database\Eloquent\Builder;

class AccountTypeFilter
{
    public function __invoke(Builder $query): Builder
    {
        if (request()->isNotFilled('account_type')) {
            return $query;
        }

        return $query->whereHas('supporter', function (Builder $query) {
            $query->where('account_type_id', request('account_type'));
        });
    }
}
