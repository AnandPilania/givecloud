<?php

namespace Ds\Services\Reports\Contributions\Filters;

use Illuminate\Database\Eloquent\Builder;

class SourceFilter
{
    public function __invoke(Builder $query): Builder
    {
        if (request()->isNotFilled('fs')) {
            return $query;
        }

        $pos = in_array('Point of Sale (POS)', request('fs'));
        $sources = array_diff(request('fs'), ['Point of Sale (POS)']);

        $query->where(function (Builder $query) use ($sources, $pos) {
            $query->when($sources, fn () => $query->whereIn('source', $sources));

            if ($pos) {
                $query->orWhere('is_pos', true);
            }
        });

        return $query;
    }
}
