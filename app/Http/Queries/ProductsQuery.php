<?php

namespace Ds\Http\Queries;

use Ds\Models\Product;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ProductsQuery extends QueryBuilder
{
    public function __construct()
    {
        parent::__construct(Product::query());

        $this
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::exact('code'),
                AllowedFilter::partial('name'),
            ])->allowedSorts([
                'name',
                'created_at',
            ])->allowedIncludes([
                'variants',
                'categories',
            ]);
    }
}
