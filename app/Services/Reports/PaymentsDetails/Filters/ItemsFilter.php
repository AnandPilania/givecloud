<?php

namespace Ds\Services\Reports\PaymentsDetails\Filters;

use Illuminate\Database\Eloquent\Builder;

class ItemsFilter
{
    public function __invoke(Builder $query): Builder
    {
        if (request()->isNotFilled('items')) {
            return $query;
        }

        return $query->when(request('items') !== '*', function (Builder $query) {
            $query->whereIn('productinventory.id', explode(',', request('items')));
        }, function (Builder $query) {
            $query->whereNotNull('productinventory.id');
        });
    }
}
