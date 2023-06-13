<?php

namespace Ds\Services\Reports\PaymentsDetails\Filters;

use Illuminate\Database\Eloquent\Builder;

class LineItemTypeFilter
{
    public function __invoke(Builder $query): Builder
    {
        if (request()->isNotFilled('line_item_type')) {
            return $query;
        }

        return $query->whereIn('type', explode(',', request('line_item_type')));
    }
}
