<?php

namespace Tests\Unit\Illuminate\Support;

use Ds\Models\Product;
use Tests\TestCase;

class CollectionMixinTest extends TestCase
{
    public function testSortByValuesUsesOrderOfValuesInArray()
    {
        $products = Product::factory(4)->create();
        $productIds = $products->pluck('id');

        $products = $products->shuffle()->sortByValues($productIds->all());

        $this->assertSame(
            $productIds->join(','),
            $products->pluck('id')->join(',')
        );
    }
}
