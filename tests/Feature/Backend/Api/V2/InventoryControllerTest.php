<?php

namespace Tests\Feature\Backend\Api\V2;

use Ds\Models\Product;
use Ds\Models\Variant;
use Illuminate\Http\Response;
use Tests\TestCase;

/**
 * @group api
 */
class InventoryControllerTest extends TestCase
{
    public function testUpdateSuccess(): void
    {
        $product = Product::factory()->create();
        $variant = Variant::factory()->create(['quantity' => 10]);
        $product->variants()->save($variant);
        $user = $this->createUserWithPermissions(['product.add']);

        /* @var \Illuminate\Testing\TestResponse */
        $this
            ->actingAsPassportUser($user)
            ->postJson(route('admin.api.v2.variants.inventory.store', ['variantHashId' => $variant->hashId]), ['quantity' => 99])
            ->assertNoContent();

        $updatedVariant = Variant::find($variant->id);
        $this->assertEquals(99, $updatedVariant->quantity);
        $this->assertEquals($user->id, $updatedVariant->quantitymodifiedbyuserid);
    }

    public function testUpdateFailsForGuest(): void
    {
        $product = Product::factory()->create();
        $variant = Variant::factory()->create(['quantity' => 10]);
        $product->variants()->save($variant);

        $this->postJson(route('admin.api.v2.variants.inventory.store', ['variantHashId' => $variant->hashId]), ['quantity' => 99])
            ->assertUnauthorized();
    }

    public function testUpdateFailsWithoutPermission(): void
    {
        $product = Product::factory()->create();
        $variant = Variant::factory()->create(['quantity' => 10]);
        $product->variants()->save($variant);

        $this->actingAsPassportUser()
            ->postJson(route('admin.api.v2.variants.inventory.store', ['variantHashId' => $variant->hashId]), ['quantity' => 99])
            ->assertForbidden();
    }

    public function testUpdateFailsWithNoQuantity(): void
    {
        $product = Product::factory()->create();
        $variant = Variant::factory()->create(['quantity' => 10]);
        $product->variants()->save($variant);
        $user = $this->createUserWithPermissions(['product.add']);

        $this->actingAsPassportUser($user)
            ->postJson(route('admin.api.v2.variants.inventory.store', ['variantHashId' => $variant->hashId]), [])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * @dataProvider updateActionQuantityDataProvider
     */
    public function testUpdateActionWithVariousQuantities($quantity, $expectedStatusCode): void
    {
        $product = Product::factory()->create();
        $variant = Variant::factory()->create(['quantity' => 9999]);

        $product->variants()->save($variant);
        $user = $this->createUserWithPermissions(['product.add']);

        $this->actingAsPassportUser($user)
            ->postJson(route('admin.api.v2.variants.inventory.store', ['variantHashId' => $variant->hashId]), ['quantity' => $quantity])
            ->assertStatus($expectedStatusCode);
    }

    public function updateActionQuantityDataProvider(): array
    {
        return [
            [null, Response::HTTP_UNPROCESSABLE_ENTITY], // quantity, statusCode
            [1.5, Response::HTTP_UNPROCESSABLE_ENTITY],
            [-1, Response::HTTP_NO_CONTENT],
        ];
    }
}
