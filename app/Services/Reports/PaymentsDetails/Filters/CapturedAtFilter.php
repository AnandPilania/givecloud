<?php

namespace Ds\Services\Reports\PaymentsDetails\Filters;

use Illuminate\Database\Eloquent\Builder;

class CapturedAtFilter
{
    public function __invoke(Builder $query): Builder
    {
        if (request()->isNotFilled(['captured_before', 'captured_after'])) {
            return $query;
        }

        $query->when(request('captured_after'), function (Builder $query) {
            $query->where('captured_at', '>=', fromLocal(request('captured_after'))->startOfDay()->toUtc());
        });

        $query->when(request('captured_before'), function (Builder $query) {
            $query->where('captured_at', '<=', fromLocal(request('captured_before'))->endOfDay()->toUtc());
        });

        return $query;
    }
}
