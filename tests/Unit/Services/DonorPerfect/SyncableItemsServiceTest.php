<?php

namespace Tests\Unit\Services\DonorPerfect;

use Ds\Domain\Sponsorship\Models\Sponsorship;
use Ds\Models\Order;
use Ds\Models\OrderItem;
use Ds\Models\Product;
use Ds\Models\Tax;
use Ds\Models\Variant;
use Ds\Services\DonorPerfect\SyncableItemsService;
use Tests\TestCase;

class SyncableItemsServiceTest extends TestCase
{
    /** @dataProvider syncableMethodsDataProvider */
    public function testSyncableMethodsReturnsFalseWhenNoItems(string $method): void
    {
        $this->assertFalse($this->app->make(SyncableItemsService::class)->{$method}(Order::factory()->create()));
    }

    public function syncableMethodsDataProvider(): array
    {
        return [
            ['hasSyncableItems'],
            ['orderHasSyncableItemsWithDcc'],
            ['orderHasSyncableShippableItems'],
            ['orderHasSyncableTaxableItems'],
        ];
    }

    public function testProductIsSyncableReturnsTrueWhenProductSyncabilityIsNotDefined(): void
    {
        $this->assertTrue($this->app->make(SyncableItemsService::class)->productIsSyncable(Product::factory()->create()));
    }

    public function testProductIsSyncableReturnsTrueWhenProductIsSyncable(): void
    {
        $product = $this->syncableProduct();

        $this->assertTrue($this->app->make(SyncableItemsService::class)->productIsSyncable($product));
    }

    public function testProductIsSyncableReturnsFalseWhenProductIsNotSyncable(): void
    {
        $product = $this->syncableProduct(false);

        $this->assertFalse($this->app->make(SyncableItemsService::class)->productIsSyncable($product));
    }

    public function testItemIsSyncableReturnsTrue(): void
    {
        $product = $this->syncableProduct();

        $item = OrderItem::factory()
            ->for(Order::factory())
            ->for(Variant::factory()->for($product))
            ->create();

        $this->assertTrue($this->app->make(SyncableItemsService::class)->itemIsSyncable($item));
    }

    public function testItemIsSyncableReturnsFalseWhenNoProductAndNoSponsorship(): void
    {
        $item = OrderItem::factory()->for(Order::factory())->create();
        $this->assertFalse($this->app->make(SyncableItemsService::class)->itemIsSyncable($item));
    }

    public function testItemIsSyncableReturnsTrueWhenSponsorship(): void
    {
        $item = OrderItem::factory()
            ->for(Sponsorship::factory())
            ->for(Order::factory())
            ->create();

        $this->assertTrue($this->app->make(SyncableItemsService::class)->itemIsSyncable($item));
    }

    /** @dataProvider syncableItemsDataProvider */
    public function testHasSyncableItemsReturnsCountOfSyncableItems(int $expected, bool $isSyncable): void
    {
        $product = $this->syncableProduct($isSyncable);

        $item = OrderItem::factory()
            ->for(Order::factory())
            ->for(Variant::factory()->for($product))
            ->create();

        $this->assertCount($expected, $this->app->make(SyncableItemsService::class)->syncableItems($item->order));
    }

    public function syncableItemsDataProvider(): array
    {
        return [
            [0, false],
            [1, true],
        ];
    }

    public function testHasSyncableItemsReturnsCountOfSyncableSponsorships(): void
    {
        $item = OrderItem::factory()
            ->for(Sponsorship::factory())
            ->for(Order::factory())
            ->create();

        $this->assertCount(1, $this->app->make(SyncableItemsService::class)->syncableItems($item->order));
    }

    public function testOrderHasSyncableItemsWithDccReturnsTrueWhenItemsHasDcc(): void
    {
        $item = OrderItem::factory()->dcc()
            ->for(Order::factory())
            ->for(Variant::factory()->for($this->syncableProduct()))
            ->create();

        $this->assertFalse($this->app->make(SyncableItemsService::class)->orderHasSyncableItemsWithDcc($item->order));

        $item->dcc_eligible = true;
        $item->save();
        $item->refresh();

        $this->assertTrue($this->app->make(SyncableItemsService::class)->orderHasSyncableItemsWithDcc($item->order));
    }

    public function testOrderHasSyncableShippableItemsReturnsTrueWhenItemsIsShippable(): void
    {
        $variant = Variant::factory()->for($this->syncableProduct())->create(['isshippable' => false]);
        $item = OrderItem::factory()->for(Order::factory())->for($variant)->create();

        $this->assertFalse($this->app->make(SyncableItemsService::class)->orderHasSyncableShippableItems($item->order));

        $variant->isshippable = true;
        $variant->save();
        $item->refresh();

        $this->assertTrue($this->app->make(SyncableItemsService::class)->orderHasSyncableShippableItems($item->order));
    }

    public function testOrderHasSyncableTaxableItemsReturnsTrueWhenItemsIsTaxable(): void
    {
        $item = OrderItem::factory()
            ->for(Order::factory())
            ->for(Variant::factory()->for($this->syncableProduct()))
            ->create();

        $this->assertFalse($this->app->make(SyncableItemsService::class)->orderHasSyncableTaxableItems($item->order));

        $item->taxes()->attach(Tax::factory()->create());
        $item->refresh();

        $this->assertTrue($this->app->make(SyncableItemsService::class)->orderHasSyncableTaxableItems($item->order));
    }

    private function syncableProduct(bool $isSyncable = true): Product
    {
        $product = Product::factory()->create();
        $product->setMetadata('dp_syncable', $isSyncable);
        $product->save();

        return $product;
    }
}
