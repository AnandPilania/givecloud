<?php

namespace Ds\Services;

use Ds\Models\Category;
use Illuminate\Support\Arr;

class FeedService
{
    /**
     * Create missing categories from their name, linked to given feed id.
     * Returns the list of category ids.
     */
    public function getOrCreateCategories(int $postTypeId, array $categoryNames = []): array
    {
        return Arr::flatten(array_map(function ($category) use ($postTypeId) {
            if (preg_match('/^category_id:(\d+)$/', $category, $matches)) {
                return  $matches[1];
            }

            return $this->getOrCreateCategoryIds($postTypeId, $category);
        }, $categoryNames));
    }

    protected function getOrCreateCategoryIds(int $postTypeId, string $categoryPath): array
    {
        return array_map(function ($categoryName) use ($postTypeId, &$parentCategory) {
            $parentCategory = Category::firstOrCreate([
                'name' => trim($categoryName),
                'assignable_type' => 'post_type',
                'assignable_id' => $postTypeId,
                'parent_id' => isset($parentCategory) ? $parentCategory->getKey() : null,
            ]);

            return $parentCategory->getKey();
        }, explode(Category::SEPARATOR, $categoryPath));
    }
}
