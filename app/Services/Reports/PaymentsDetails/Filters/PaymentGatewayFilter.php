<?php

namespace Ds\Services\Reports\PaymentsDetails\Filters;

use Illuminate\Database\Eloquent\Builder;

class PaymentGatewayFilter
{
    public function __invoke(Builder $query): Builder
    {
        if (request()->isNotFilled('gateway')) {
            return $query;
        }

        return $query->whereHas('ledgerable', function (Builder $query) {
            $query->whereHas('payments', function (Builder $query) {
                $query->where('gateway_type', request('gateway'));
            });
        });
    }
}
