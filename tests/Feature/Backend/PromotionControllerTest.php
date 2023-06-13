<?php

namespace Tests\Feature\Backend;

use Ds\Models\Membership;
use Ds\Models\Product;
use Ds\Models\ProductCategory;
use Ds\Models\PromoCode;
use Tests\TestCase;

/**
 * @group backend
 * @group promotion
 */
class PromotionControllerTest extends TestCase
{
    public function testSaveNewPromotionSuccess()
    {
        sys_set('feature_promos', 1);

        $promoCodeData = PromoCode::factory()->make();

        $this->postNewPromotion($promoCodeData->toArray());

        $this->assertPromoCode($promoCodeData);
    }

    public function testSaveNewPromotionWithNullMembershipsSuccess()
    {
        sys_set('feature_promos', 1);

        $promoCodeData = PromoCode::factory()->make();

        $this->postNewPromotion($promoCodeData->toArray() + ['membership_ids' => [null]]);

        $savedPromoCode = $this->assertPromoCode($promoCodeData);
        $this->assertEmpty($savedPromoCode->memberships);
    }

    public function testSaveNewPromotionWithMembershipsSuccess()
    {
        sys_set('feature_promos', 1);

        $promoCodeData = PromoCode::factory()->make();
        $membershipIds = Membership::factory(3)->create()->map->getKey();

        $this->postNewPromotion($promoCodeData->toArray() + ['membership_ids' => $membershipIds]);

        $savedPromoCode = $this->assertPromoCode($promoCodeData);
        $this->assertEquals($membershipIds, $savedPromoCode->memberships->map->getKey());
    }

    private function postNewPromotion(array $dataToSend): void
    {
        $this->actingAsUser($this->createUserWithPermissions(['promocode.add']))
            ->post(route('backend.promotions.save'), $dataToSend)
            ->assertRedirect(route('backend.promotions.index'));
    }

    private function assertPromoCode(PromoCode $promoCode): PromoCode
    {
        $savedPromoCode = PromoCode::where('code', $promoCode->code)->firstOrFail();
        foreach ($promoCode->toArray() as $attribute => $value) {
            $this->assertSame($value, $savedPromoCode->{$attribute});
        }

        return $savedPromoCode;
    }

    public function testDuplicatePromoCodeSuccess()
    {
        sys_set('feature_promos', 1);

        $originalPromoCode = PromoCode::factory()->create(['usage_limit' => 99]);
        $membershipIds = Membership::factory(3)->create()->map->getKey();
        $categoryIds = ProductCategory::factory(3)->create()->map->getKey();
        $productIds = Product::factory(3)->create()->map->getKey();

        $originalPromoCode->memberships()->sync($membershipIds);
        $originalPromoCode->categories()->sync($categoryIds);
        $originalPromoCode->products()->sync($productIds);

        $response = $this->actingAsUser($this->createUserWithPermissions(['promocode.add']))
            ->post(route('backend.promotions.duplicate', [$originalPromoCode]), ['new_code' => 'AWESOME_PROMO']);

        $newPromoCode = PromoCode::where('code', 'AWESOME_PROMO')->first();

        $response->assertRedirect(route('backend.promotions.edit', [$newPromoCode]));

        $this->assertEquals($newPromoCode->usage_limit, 99);
        $this->assertEquals($membershipIds, $newPromoCode->memberships->map->getKey());
        $this->assertEquals($categoryIds, $newPromoCode->categories->map->getKey());
        $this->assertEquals($productIds, $newPromoCode->products->map->getKey());
    }

    public function testCannotDuplicateSamePromoCodeSuccess()
    {
        sys_set('feature_promos', 1);

        $promoCode = PromoCode::factory()->create();

        $this->actingAsUser($this->createUserWithPermissions(['promocode.add']))
            ->post(route('backend.promotions.duplicate', [$promoCode]), ['new_code' => $promoCode->code])
            ->assertRedirect();

        $this->assertEquals(PromoCode::count(), 1);
    }
}
