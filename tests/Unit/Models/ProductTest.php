<?php

namespace Tests\Unit\Models;

use Ds\Models\Product;
use Tests\TestCase;

class ProductTest extends TestCase
{
    public function testDesignationOptionsUsesMeta1AsFallback(): void
    {
        $product = Product::factory()->create(['meta1' => 'GENERAL_FUND']);

        $this->assertSame('GENERAL_FUND', $product->designation_options->default_account ?? null);
        $this->assertSame('GENERAL_FUND', $product->designation_options->designations[0]->account ?? null);
        $this->assertTrue($product->designation_options->designations[0]->is_default ?? null);
    }
}
