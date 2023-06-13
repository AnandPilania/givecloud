<?php

namespace Ds\Repositories;

use Ds\Models\ProductCategory;
use Illuminate\Support\Collection;

class ProductCategoryRepository
{
    public function getProductCategoryList(int $parent = null): Collection
    {
        $categories = ProductCategory::query()
            ->select('id', 'name', 'url_name')
            ->when(
                $parent === null,
                function ($query) {
                    $query->whereNull('parent_id');
                    $query->orWhere('parent_id', 0);
                },
                function ($query) use ($parent) {
                    $query->where('parent_id', $parent);
                },
            )->orderBy('sequence')
            ->toBase()
            ->get();

        $categories->each(function ($category) {
            $category->categories = $this->getProductCategoryList($category->id);
        });

        return $categories;
    }
}
