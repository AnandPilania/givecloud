<?php

namespace Ds\Services\Reports\Contributions\Filters;

use Illuminate\Database\Eloquent\Builder;

class TrackingSourceFilter
{
    public function __invoke(Builder $query): Builder
    {
        if (request()->isNotFilled('fots')) {
            return $query;
        }

        $query->whereRaw('contributions.tracking_source LIKE ?', ['%' . request('fots') . '%']);

        return $query;
    }
}
