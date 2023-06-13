<?php

namespace Tests\Unit\Models;

use Ds\Models\Product;
use Ds\Models\Variant;
use Tests\TestCase;

class VariantTest extends TestCase
{
    /** @dataProvider maximumQuantityAvailableForPurchaseDataProvider */
    public function testMaximumQuantityAvailableForPurchaseReturnsMaximum(int $quantity): void
    {
        $variant = Variant::factory()->forProduct()->create(['quantity' => $quantity]);

        $this->assertSame($quantity, $variant->maximumQuantityAvailableForPurchase);
    }

    public function maximumQuantityAvailableForPurchaseDataProvider(): array
    {
        return [
            [1],
            [4],
            [0],
            [1000],
        ];
    }

    /** @dataProvider maximumQuantityAvailableForBundleDataProvider */
    public function testMaximumQuantityAvailableForBundle(int $variantQty, int $bundleQty, int $inBundleQty, int $expected): void
    {
        Product::factory()->allowOutOfStock()->create()->variants()->saveMany([
            $variant = Variant::factory()->create(['quantity' => $variantQty]),
            $variantBundle = Variant::factory()->create(['quantity' => $bundleQty]),
        ]);

        // For a single variant bundle, we need 10 of the variant.
        // With 40 in stock fo that variant, maximum purchasable of the bundle is 10
        $variantBundle->linkedVariants()->attach($variant->getKey(), ['qty' => $inBundleQty]);

        $this->assertSame($expected, $variantBundle->maximumQuantityAvailableForPurchase);
    }

    public function maximumQuantityAvailableForBundleDataProvider(): array
    {
        return [
            [40, 5, 10, 4],
            [27, 2, 10, 2],
            [7, 2, 10, 0], // not enough of variant in bundle
            [40, 0, 10, 0], // no bundle
            [0, 5, 10, 0], // no variant in bundle
        ];
    }
}
