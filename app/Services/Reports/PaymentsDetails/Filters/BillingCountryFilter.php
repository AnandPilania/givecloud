<?php

namespace Ds\Services\Reports\PaymentsDetails\Filters;

use Illuminate\Database\Eloquent\Builder;

class BillingCountryFilter
{
    public function __invoke(Builder $query): Builder
    {
        if (request()->isNotFilled('billing_country')) {
            return $query;
        }

        return $query->whereHas('order', function (Builder $query) {
            $query->where('billingcountry', request('billing_country'));
        });
    }
}
