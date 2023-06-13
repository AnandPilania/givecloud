<?php

namespace Tests\Feature\Backend;

use Ds\Models\Product;
use Ds\Models\ProductCategory;
use Ds\Models\Variant;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    public function testCanFilterOnNotSyncableProducts(): void
    {
        Product::factory(10)->create();

        $product = Product::factory()->hasMetadataRelation(1, [
            'key' => 'dp_syncable',
            'value' => '0',
        ])->create();

        $response = $this->actingAsAdminUser()
            ->post(route('backend.products.ajax'), ['dp_sync' => '0']);

        $response->assertJsonCount(1, 'data');

        $this->assertTrue(Str::contains($response->json('data.0.0'), $product->code));
    }

    public function testCanFilterOnlyDpSyncableProducts(): void
    {
        // We already have seeded product
        $existingProducts = $this->actingAsAdminUser()->post(route('backend.products.ajax'))->json('data');

        Product::factory()->hasMetadataRelation(1, [
            'key' => 'dp_syncable',
            'value' => '1',
        ])->create();

        Product::factory()->hasMetadataRelation(1, [
            'key' => 'dp_syncable',
            'value' => '0',
        ])->create();

        $response = $this->actingAsAdminUser()
            ->post(route('backend.products.ajax'), ['dp_sync' => '1']);

        $response->assertJsonCount(count($existingProducts) + 1, 'data');
    }

    public function testCanFilterOnAllDpSyncableStatuses(): void
    {
        // We already have seeded product
        $existingProducts = $this->actingAsAdminUser()->post(route('backend.products.ajax'))->json('data');

        Product::factory()->hasMetadataRelation(1, [
            'key' => 'dp_syncable',
            'value' => '1',
        ])->create();

        Product::factory()->hasMetadataRelation(1, [
            'key' => 'dp_syncable',
            'value' => '0',
        ])->create();

        $response = $this->actingAsAdminUser()
            ->post(route('backend.products.ajax'), ['dp_sync' => null]);

        $response->assertJsonCount(count($existingProducts) + 2, 'data');
    }

    public function testSavingVariantMetadata()
    {
        $product = $this->createProductWithVariant();
        $variants = json_decode($product->variants->toJson(), true);

        $variants[0]['metadata']['redirects_to'] = 'https://google.ca';

        $this->postToSave(array_merge($product->toArray(), [
            'base_currency' => (string) $product->base_currency,
            'variant_json' => json_encode($variants),
        ]));

        $product->refresh();

        $this->assertSame('https://google.ca', $product->variants[0]->metadata('redirects_to'));
    }

    public function testProductSavesWithWrongMediaIds()
    {
        $newProduct = Product::factory()->make();

        $response = $this->postToSave(array_merge($newProduct->toArray(), [
            // base_currency is an instance of \Ds\Domain\Commerce\Currency
            // but we need the code string in the payload
            'base_currency' => $newProduct->base_currency->code,
            'variant_json' => $this->getFormVariantJson([
                'media' => [['id' => 0, 'caption' => null]], // unknown media id
            ]),
        ]));

        // Fetch newly created Product
        $newProductModel = Product::where('code', $newProduct->code)
            ->where('name', $newProduct->name)
            ->where('summary', $newProduct->summary)
            ->firstOrFail();

        $this->assertProductSaved($response, $newProductModel->getKey());
    }

    public function testProductSavesWithWrongPhotoIds(): void
    {
        $newProduct = Product::factory()->make();

        $response = $this->postToSave(array_merge($newProduct->toArray(), [
            // base_currency is an instance of \Ds\Domain\Commerce\Currency
            // but we need the code string in the payload
            'base_currency' => $newProduct->base_currency->code,
            'variant_json' => $this->getFormVariantJson(),
            'photo_id' => 0, // unkown Media
        ]));

        // Fetch newly created Product
        $newProductModel = Product::where('code', $newProduct->code)
            ->where('name', $newProduct->name)
            ->where('summary', $newProduct->summary)
            ->firstOrFail();

        $this->assertProductSaved($response, $newProductModel->getKey());
    }

    public function testSaveWithCategories()
    {
        $newProduct = Product::factory()->make();
        $categories = ProductCategory::factory(3)->create();

        $response = $this->postToSave(array_merge($newProduct->toArray(), [
            // base_currency is an instance of \Ds\Domain\Commerce\Currency
            // but we need the code string in the payload
            'base_currency' => $newProduct->base_currency->code,
            'variant_json' => $this->getFormVariantJson(),
            'category' => $categories->map->getKey(),
        ]));

        // Fetch newly created Product
        $newProductModel = Product::where('code', $newProduct->code)
            ->where('name', $newProduct->name)
            ->where('summary', $newProduct->summary)
            ->firstOrFail();

        $this->assertProductSaved($response, $newProductModel->getKey());
        $this->assertEquals($categories->map->getKey(), $newProductModel->categories->map->getKey());
    }

    public function testSaveWithNullCategory()
    {
        $newProduct = Product::factory()->make();

        $response = $this->postToSave(array_merge($newProduct->toArray(), [
            // base_currency is an instance of \Ds\Domain\Commerce\Currency
            // but we need the code string in the payload
            'base_currency' => $newProduct->base_currency->code,
            'variant_json' => $this->getFormVariantJson(),
            'category' => [null],
        ]));

        // Fetch newly created Product
        $newProductModel = Product::where('code', $newProduct->code)
            ->where('name', $newProduct->name)
            ->where('summary', $newProduct->summary)
            ->firstOrFail();

        $this->assertProductSaved($response, $newProductModel->getKey());
        $this->assertEmpty($newProductModel->categories->toArray());
    }

    public function testSaveSimpleCustomFields()
    {
        $product = $this->createProductWithVariant();
        $options = ['first option', 'second option', 'third'];

        $response = $this->postToSave($this->getProductCustomFieldsToSave($product, [
            'options' => implode("\r\n", $options),
            'default_value' => end($options),
        ]));

        $this->assertProductSaved($response, $product->getKey());

        $this->assertProductChoicesSaved(
            $product,
            array_map(function ($option) { return (object) ['label' => $option, 'value' => $option]; }, $options)
        );
    }

    public function testSaveAdvancedCustomFields()
    {
        $product = $this->createProductWithVariant();
        $options = [
            ['label' => 'first option label', 'value' => 'first option'],
            ['label' => 'second option label', 'value' => 'second option'],
            ['label' => 'third label', 'value' => 'third'],
        ];

        $response = $this->postToSave($this->getProductCustomFieldsToSave($product, [
            'format' => 'advanced',
            'options' => json_encode($options),
            'choices' => $options,
            'default_value' => end($options)['value'],
        ]));

        $this->assertProductSaved($response, $product->getKey());

        $this->assertProductChoicesSaved(
            $product,
            array_map(function ($option) { return (object) $option; }, $options)
        );
    }

    private function assertProductChoicesSaved(Product $product, array $options): void
    {
        $productFirstCustomField = $product->refresh()->customFields->first();
        $productChoices = $productFirstCustomField['choices'];

        $this->assertNotEmpty($productChoices);
        $this->assertEquals($options, $productChoices);
        $this->assertSame(end($options)->value, $productFirstCustomField->default_value);
    }

    private function assertProductSaved(TestResponse $response, int $productId): TestResponse
    {
        return $response
            ->assertRedirect(route('backend.products.edit', 's&i=' . $productId))
            ->assertSessionHas('_flashMessages.success', 'Item saved.');
    }

    private function createProductWithVariant(): Product
    {
        $product = Product::factory()->create();
        $variant = Variant::factory()->create();
        $product->variants()->save($variant);

        return $product;
    }

    private function getProductCustomFieldsToSave(Product $product, array $firstProductFields = []): array
    {
        return array_merge($product->toArray(), [
            'base_currency' => $product->base_currency->code,
            'productfields' => [
                array_merge([
                    '_isnew' => '1',
                    'sequence' => '',
                    'type' => 'select',
                    'name' => 'test',
                    'isrequired' => '1',
                    'format' => '',
                    'options' => '',
                    'default_value' => '',
                    'map_to_product_meta' => '',
                    'body' => '',
                    'hint' => '',
                ], $firstProductFields),
            ],
        ]);
    }

    private function postToSave(array $formData): TestResponse
    {
        return $this
            ->actingAsUser()
            ->post(route('backend.products.save'), $formData);
    }

    private function getFormVariantJson(array $attributes = []): string
    {
        return json_encode([array_merge(
            Variant::factory()->make()->toArray(),
            [
                // attributes not present in the factory
                'saleprice' => null,
                'quantityrestock' => null,
                'shipping_expectation_threshold' => null,
                'shipping_expectation_over' => null,
                'shipping_expectation_under' => null,
                'membership_id' => null,
                'cost' => null,
                'sku' => null,
                'fair_market_value' => null,
                'billing_period' => 'onetime',
                'billing_starts_on' => null,
                'billing_ends_on' => null,
                'total_billing_cycles' => null,
                'price_presets' => null,
                'price_minimum' => null,
                'total_billing_cycles' => null,
                'total_billing_cycles' => null,

                // special control/form attributes
                // @see app/Http/Controllers/ProductController.php
                '_is_new' => true,
                '_update_quantity' => null,
                'file' => null,
                'media' => null,
                'metadata' => null,
            ],
            $attributes,
        )]);
    }
}
