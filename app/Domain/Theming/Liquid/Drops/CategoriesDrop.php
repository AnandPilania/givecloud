<?php

namespace Ds\Domain\Theming\Liquid\Drops;

use Ds\Domain\Theming\Liquid\Drop;
use Illuminate\Support\Str;

class CategoriesDrop extends Drop
{
    const SOURCE_REQUIRED = false;

    /**
     * Catch all method that is invoked before a specific method
     *
     * @param string $method
     * @return mixed
     */
    protected function liquidMethodMissing($method)
    {
        $categories = \Ds\Models\ProductCategory::get();

        foreach ($categories as $category) {
            if ($method === Str::slug($category->name)) {
                return new CategoryDrop($category);
            }
        }

        return null;
    }
}
