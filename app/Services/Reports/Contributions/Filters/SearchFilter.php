<?php

namespace Ds\Services\Reports\Contributions\Filters;

use Illuminate\Database\Eloquent\Builder;

class SearchFilter
{
    public function __invoke(Builder $query): Builder
    {
        if (request()->isNotFilled('fO')) {
            return $query;
        }

        $query->where(function (Builder $query) {
            foreach (explode(' ', request('fO')) as $keyword) {
                $query->orWhereRaw('searchable_text LIKE ?', ['%' . $keyword . '%']);
            }
        });

        return $query;
    }
}
