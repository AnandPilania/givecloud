<?php

namespace Tests\Unit\Models;

use Ds\Models\Category;
use Ds\Models\Post;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    public function testCorrectFullnameWhenMainCategory()
    {
        $category = Category::factory()->create();

        $this->assertSame($category->name, $category->fullName());
    }

    public function testCorrectFullnameWhenSubCategory()
    {
        $mainCategory = Category::factory()->create();
        $subCategory = Category::factory()->create();
        $mainCategory->childCategories()->save($subCategory);

        $this->assertSame($mainCategory->name . ' ' . Category::SEPARATOR . ' ' . $subCategory->name, $subCategory->fullName());
    }

    public function testDeleteCategoryDeletesSubCategories()
    {
        $mainCategory = Category::factory()->create();
        $subCategories = Category::factory(3)->create();
        $mainCategory->childCategories()->saveMany($subCategories);

        $mainCategory->delete();

        // Check main category has been deleted.
        $this->assertDatabaseMissing($mainCategory->getTable(), [
            $mainCategory->getKeyName() => $mainCategory->getKey(),
        ]);

        // Check sub categories have been deleted as well.
        $subCategories->each(function ($category) {
            $this->assertDatabaseMissing($category->getTable(), [
                $category->getKeyName() => $category->getKey(),
            ]);
        });
    }

    public function testDeleteCategoryDeletesSubSubCategories()
    {
        $mainCategory = Category::factory()->create();
        $subCategories = Category::factory(2)->create();
        $subSubCategories = Category::factory(2)->create();
        $subCategories->first()->childCategories()->saveMany($subSubCategories);
        $mainCategory->childCategories()->saveMany($subCategories);

        $mainCategory->delete();

        // Check main category has been deleted.
        $this->assertDatabaseMissing($mainCategory->getTable(), [
            $mainCategory->getKeyName() => $mainCategory->getKey(),
        ]);

        // Check sub categories have been deleted as well.
        $subCategories->each(function ($category) {
            $this->assertDatabaseMissing($category->getTable(), [
                $category->getKeyName() => $category->getKey(),
            ]);
        });

        // Check sub sub categories have been deleted as well.
        $subSubCategories->each(function ($category) {
            $this->assertDatabaseMissing($category->getTable(), [
                $category->getKeyName() => $category->getKey(),
            ]);
        });
    }

    public function testDeleteCategoryUnlinkPosts()
    {
        $mainCategory = Category::factory()->create();
        $posts = Post::factory(3)->create();
        $mainCategory->posts()->attach($posts);

        $mainCategory->delete();

        // Check main category has been deleted.
        $this->assertDatabaseMissing($mainCategory->getTable(), [
            $mainCategory->getKeyName() => $mainCategory->getKey(),
        ]);

        // Check sub categories have been deleted as well.
        $posts->each(function ($post) {
            $this->assertDatabaseHas($post->getTable(), [
                $post->getKeyName() => $post->getKey(),
            ]);
            $this->assertEmpty($post->refresh()->categories);
        });
    }
}
