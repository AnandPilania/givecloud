<?php

namespace Tests\Unit\Services;

use Ds\Models\Category;
use Ds\Models\PostType;
use Ds\Services\FeedService;
use Tests\TestCase;

/**
 * @group backend
 * @group services
 * @group feed
 */
class FeedServiceTest extends TestCase
{
    public function testUpdateFeedWithNewMainCategory()
    {
        $feed = PostType::factory()->create();
        $newCategoryName = 'main category';

        $this->updateCategories($feed, [$newCategoryName]);

        $this->assertFeedNotEmpty($feed);
        $this->assertSame($newCategoryName, $feed->categories->first()->name);
    }

    public function testUpdateFeedWithNewSubCategory()
    {
        $mainCategory = $this->createCategory();
        $newSubCategoryName = 'sub category';

        $feed = PostType::factory()->create();
        $feed->categories()->save($mainCategory);

        $this->updateCategories($feed, [$mainCategory->name . Category::SEPARATOR . $newSubCategoryName]);

        $this->assertFeedNotEmpty($feed);
        $this->assertCategory($feed, $mainCategory, $newSubCategoryName);
    }

    public function testUpdateFeedWithNewSubCategoryAndSpaces()
    {
        $mainCategory = $this->createCategory();
        $newSubCategoryName = 'sub category';

        $feed = PostType::factory()->create();
        $feed->categories()->save($mainCategory);

        $this->updateCategories($feed, [$mainCategory->name . ' ' . Category::SEPARATOR . ' ' . $newSubCategoryName]);

        $this->assertFeedNotEmpty($feed);
        $this->assertCategory($feed, $mainCategory, $newSubCategoryName);
    }

    public function testUpdateFeedWithNew2SubCategories()
    {
        $mainCategory = $this->createCategory();
        $newSubCategoryName = 'sub category';
        $newSubCategoryTwoName = 'sub category2';

        $feed = PostType::factory()->create();
        $feed->categories()->save($mainCategory);

        $this->updateCategories($feed, [
            $mainCategory->name . Category::SEPARATOR . $newSubCategoryName,
            $mainCategory->name . Category::SEPARATOR . $newSubCategoryTwoName,
        ]);

        $this->assertFeedNotEmpty($feed);
        $this->assertCategory($feed, $mainCategory, $newSubCategoryName);
        $this->assertCategory($feed, $mainCategory, $newSubCategoryTwoName);
    }

    public function testUpdateFeedWithNewSubSubCategory()
    {
        $mainCategory = $this->createCategory();
        $subCategory = $this->createCategory();
        $mainCategory->childCategories()->save($subCategory);
        $newSubSubCategoryName = 'sub sub category';

        $feed = PostType::factory()->create();
        $feed->categories()->saveMany([$mainCategory, $subCategory]);

        $this->updateCategories($feed, [
            $mainCategory->name . Category::SEPARATOR . $subCategory->name . Category::SEPARATOR . $newSubSubCategoryName,
        ]);

        $this->assertFeedNotEmpty($feed);
        $this->assertCategory($feed, $subCategory, $newSubSubCategoryName);
    }

    public function testUpdateFeedWithNewSubCategoryAndSubSubCategory()
    {
        $mainCategory = $this->createCategory();
        $newSubCategoryName = 'sub category';
        $newSubSubCategoryName = 'sub sub category';

        $feed = PostType::factory()->create();
        $feed->categories()->save($mainCategory);

        $this->updateCategories($feed, [
            'category_id:' . $mainCategory->getKey(),
            $mainCategory->name . Category::SEPARATOR . $newSubCategoryName . Category::SEPARATOR . $newSubSubCategoryName,
        ]);

        $this->assertFeedNotEmpty($feed);
        $savedSubCategory = $this->assertCategory($feed, $mainCategory, $newSubCategoryName);
        $this->assertCategory($feed, $savedSubCategory, $newSubSubCategoryName);
    }

    private function assertCategory(PostType $feed, Category $parentCategory, string $categoryName): Category
    {
        $savedCategory = $feed->categories->filter(function ($category) use ($categoryName) {
            return $category->name === $categoryName;
        })->first();

        $this->assertNotEmpty($savedCategory);
        $this->assertSame($parentCategory->getKey(), $savedCategory->parentCategory->getKey());

        return $savedCategory;
    }

    private function assertFeedNotEmpty(PostType $feed): void
    {
        $this->assertNotEmpty($feed->refresh()->categories->all());
    }

    private function createCategory()
    {
        return Category::factory()->create(['assignable_type' => 'post_type']);
    }

    private function updateCategories(PostType $feed, array $categories = []): array
    {
        return (new FeedService())->getOrCreateCategories($feed->getKey(), $categories);
    }
}
