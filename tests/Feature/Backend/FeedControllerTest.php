<?php

namespace Tests\Feature\Backend;

use Ds\Models\Category;
use Ds\Models\PostType;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * @group backend
 * @group feed
 */
class FeedControllerTest extends TestCase
{
    public function testUpdateFeedWithNewMainCategory(): void
    {
        $feed = PostType::factory()->create();
        $newCategoryName = 'new category';

        $this->updateCategories($feed, [$newCategoryName]);

        $this->assertNotEmpty($feed->refresh()->categories->all());
        $this->assertSame($newCategoryName, $feed->categories->first()->name);
    }

    public function testUpdateFeedWithNewSubCategory(): void
    {
        $mainCategory = Category::factory()->create(['assignable_type' => 'post_type']);
        $newSubCategoryName = 'sub category';

        $feed = PostType::factory()->create();
        $feed->categories()->save($mainCategory);

        $this->updateCategories($feed, [$mainCategory->name . Category::SEPARATOR . $newSubCategoryName]);

        $this->assertNotEmpty($feed->refresh()->categories->all());

        // Assert sub category
        $savedCategory = $feed->categories->filter(function ($category) use ($newSubCategoryName) {
            return $category->name === $newSubCategoryName;
        })->first();
        $this->assertNotEmpty($savedCategory);
        $this->assertSame($mainCategory->getKey(), $savedCategory->parentCategory->getKey());
    }

    public function testDestroyFeedAndRedirectsToFeedIndex(): void
    {
        $feed = PostType::factory()->create();

        $this
            ->actingAsUser($this->createUserWithPermissions('posttype.edit'))
            ->post(route('backend.feeds.destroy'), ['id' => $feed->getKey()])
            ->assertRedirect(route('backend.feeds.index'));
    }

    private function updateCategories(PostType $feed, array $categories = []): TestResponse
    {
        return $this
            ->actingAsUser($this->createUserWithPermissions('posttype.edit'))
            ->post(route('backend.feeds.update'), [
                'id' => $feed->getKey(),
                'categories' => $categories,
            ])->assertRedirect(route('backend.feeds.edit', ['i' => $feed->getKey()]));
    }
}
