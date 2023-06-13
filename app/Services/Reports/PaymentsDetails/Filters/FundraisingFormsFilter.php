<?php

namespace Ds\Services\Reports\PaymentsDetails\Filters;

use Ds\Models\Product;
use Illuminate\Database\Eloquent\Builder;

class FundraisingFormsFilter
{
    public function __invoke(Builder $query): Builder
    {
        if (request()->isNotFilled('fundraising_forms')) {
            return $query;
        }

        $ids = collect(explode(',', request('fundraising_forms')))->map(fn ($hash) => Product::decodeHashid($hash));

        return $query->whereIn('productinventory.productid', $ids);
    }
}
