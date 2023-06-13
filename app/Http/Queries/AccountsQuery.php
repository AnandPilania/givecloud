<?php

namespace Ds\Http\Queries;

use Ds\Models\Account;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class AccountsQuery extends QueryBuilder
{
    public function __construct()
    {
        parent::__construct(Account::query()->with([
            'accountType',
            'groupAccountTimespans',
        ]));

        $this
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::partial('name', 'display_name'),
                AllowedFilter::partial('email'),
                AllowedFilter::scope('recurringProfile', 'recurringPaymentProfiles.status'),
            ])->allowedSorts([])
            ->allowedIncludes([]);
    }
}
