<?php

namespace Ds\Services\Reports\PaymentsDetails\Filters;

use Illuminate\Database\Eloquent\Builder;

class SponsorshipFilter
{
    public function __invoke(Builder $query): Builder
    {
        if (request()->isNotFilled('sponsorship')) {
            return $query;
        }

        return $query->whereNotNull('ledger_entries.sponsorship_id')
            ->when(request('sponsorship') !== '*', function (Builder $query) {
                $query->whereIn('ledger_entries.sponsorship_id', explode(',', request('sponsorship')));
            });
    }
}
