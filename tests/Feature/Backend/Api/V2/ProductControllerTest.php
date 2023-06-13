<?php

namespace Tests\Feature\Backend\Api\V2;

use Ds\Models\Product;
use Tests\TestCase;

/**
 * @group api
 */
class ProductControllerTest extends TestCase
{
    public function testIndexSuccess(): void
    {
        Product::factory(3)->create();
        $productsCount = Product::count();

        /** @var \Illuminate\Testing\TestResponse */
        $response = $this
            ->actingAsPassportUser($this->createUserWithPermissions(['product.view']))
            ->getJson(route('admin.api.v2.products.index'))
            ->assertOk();

        $jsonResponse = $response->decodeResponseJson();
        $jsonResponse->assertStructure(['data', 'links', 'meta']);
        $jsonResponse->assertCount($productsCount, 'data');
    }

    public function testIndexFailsForGuest(): void
    {
        $this->getJson(route('admin.api.v2.products.index'))
            ->assertUnauthorized();
    }

    public function testIndexFailsWithoutPermission(): void
    {
        $this->actingAsPassportUser()
            ->getJson(route('admin.api.v2.products.index'))
            ->assertForbidden();
    }

    public function testShowSuccess(): void
    {
        /** @var \Ds\Models\Product */
        $product = Product::factory()->create(['summary' => 'My awesome product']);

        /** @var \Illuminate\Testing\AssertableJsonString */
        $jsonResponse = $this
            ->actingAsPassportUser($this->createUserWithPermissions(['product.view']))
            ->getJson(route('admin.api.v2.products.show', $product->hashid))
            ->assertOk()
            ->decodeResponseJson();

        $jsonResponse->assertStructure(['data']);
        $this->assertSame($product->hashid, $jsonResponse->json('data.id'));
        $this->assertSame($product->summary, $jsonResponse->json('data.description'));
    }

    public function testShowFailsForGuest(): void
    {
        $this
            ->getJson(route('admin.api.v2.products.show', Product::factory()->create()->first()->hashid))
            ->assertUnauthorized();
    }

    public function testShowFailsWithoutPermission(): void
    {
        $this
            ->actingAsPassportUser()
            ->getJson(route('admin.api.v2.products.show', Product::factory()->create()->first()->hashid))
            ->assertForbidden();
    }
}
