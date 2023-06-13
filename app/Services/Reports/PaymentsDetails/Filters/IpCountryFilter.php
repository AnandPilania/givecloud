<?php

namespace Ds\Services\Reports\PaymentsDetails\Filters;

use Illuminate\Database\Eloquent\Builder;

class IpCountryFilter
{
    public function __invoke(Builder $query): Builder
    {
        if (request()->isNotFilled('ip_country')) {
            return $query;
        }

        return $query->whereHas('order', function (Builder $query) {
            $query->where('ip_country', request('ip_country'));
        });
    }
}
