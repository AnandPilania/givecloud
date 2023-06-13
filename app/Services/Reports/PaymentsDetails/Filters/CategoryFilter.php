<?php

namespace Ds\Services\Reports\PaymentsDetails\Filters;

use Illuminate\Database\Eloquent\Builder;

class CategoryFilter
{
    public function __invoke(Builder $query): Builder
    {
        if (request()->isNotFilled('categories')) {
            return $query;
        }

        return $query->whereHas('item', function (Builder $query) {
            $query->whereHas('variant', function (Builder $query) {
                $query->whereHas('product', function (Builder $query) {
                    $query->whereHas('categories', function (Builder $query) {
                        $query->whereIn('productcategory.id', explode(',', request('categories')));
                    });
                });
            });
        });
    }
}
