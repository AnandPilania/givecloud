<?php

namespace Tests\Feature\Frontend;

use Ds\Models\Product;
use Ds\Models\ProductCategory;
use Illuminate\Support\Str;
use Tests\TestCase;

class DefaultControllerTest extends TestCase
{
    public function testCategoryUrlName()
    {
        $category = ProductCategory::factory()->create();

        $this->get($category->url)
            ->assertOk()
            ->assertSeeText($category->name);
    }

    public function testCategoryUrlWithProductsPrefix()
    {
        $category = ProductCategory::factory()->create([
            'url_name' => 'products/' . Str::random(60),
        ]);

        $this->get($category->url)
            ->assertOk()
            ->assertSeeText($category->name);
    }

    public function testProductPermalink()
    {
        $product = Product::factory()->permalink()->create();

        $this->get("/{$product->permalink}")
            ->assertOk()
            ->assertSeeText($product->name);
    }

    public function testProductDefaultUrlPrefixSingular()
    {
        $product = Product::factory()->create();

        $this->get("/product/{$product->code}/ut-cursus-dignissim-neque-non")
            ->assertOk()
            ->assertSeeText($product->name);
    }

    public function testProductDefaultUrlPrefixPlural()
    {
        $product = Product::factory()->create();

        $this->get("/products/{$product->code}/nunc-volutpat-nunc-nec-dui")
            ->assertOk()
            ->assertSeeText($product->name);
    }
}
