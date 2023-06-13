<?php

namespace Ds\Services\Reports\PaymentsDetails\Filters;

use Illuminate\Database\Eloquent\Builder;

class PaymentMethodFilter
{
    public function __invoke(Builder $query): Builder
    {
        if (request()->isNotFilled('payment_method')) {
            return $query;
        }

        return $query->whereHas('ledgerable', function (Builder $query) {
            $query->whereHas('payments', function (Builder $query) {
                $query->where('type', request('payment_method'));
            });
        });
    }
}
