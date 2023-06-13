<?php

namespace Tests\Unit\Services;

use Ds\Enums\ExternalReference\ExternalReferenceService;
use Ds\Enums\ExternalReference\ExternalReferenceType;
use Ds\Models\ExternalReference;
use Ds\Models\Order;
use Ds\Models\OrderItem;
use Ds\Models\Transaction;
use Ds\Services\ExternalReferencesService;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ExternalReferencesServiceTest extends TestCase
{
    use WithFaker;

    public function testCanDeleteExternalReferenceByCoding(): void
    {
        $order = Order::factory()->create();

        ExternalReference::factory()->for($order, 'referenceable')->create([
            'reference' => $this->faker->uuid,
            'service' => ExternalReferenceService::DONOR_PERFECT,
            'type' => ExternalReferenceType::ORDER,
        ]);

        $this->assertDatabaseHas('external_references', [
            'referenceable_type' => $order->getMorphClass(),
            'referenceable_id' => $order->getKey(),
            'service' => ExternalReferenceService::DONOR_PERFECT,
            'type' => ExternalReferenceType::ORDER,
        ]);

        $this->app->make(ExternalReferencesService::class)->deleteByCoding('GC:1:ORDER:' . $order->getKey());

        $this->assertDatabaseMissing('external_references', [
            'referenceable_type' => $order->getMorphClass(),
            'referenceable_id' => $order->getKey(),
            'service' => ExternalReferenceService::DONOR_PERFECT,
            'type' => ExternalReferenceType::ORDER,
        ]);
    }

    public function testUpsertReturnsExternalReference(): void
    {
        $order = Order::factory()->create();

        $model = $this->app->make(ExternalReferencesService::class)->upsert($order, $this->faker->uuid);

        $this->assertInstanceOf(ExternalReference::class, $model);
    }

    /**
     * @dataProvider codePrefixDataProvider
     */
    public function testReturnsReferenceByCoding(string $codePrefix, string $factory, string $type): void
    {
        // No need for OrderItemObserver to raise events.
        Event::fake();

        $model = (new $factory)->factory();

        if ($factory === OrderItem::class) {
            $model = $model->for(Order::factory());
        }

        $model = $model->create();

        $externalReference = $this->faker->uuid;

        ExternalReference::factory()->for($model, 'referenceable')->create([
            'reference' => $externalReference,
            'service' => ExternalReferenceService::DONOR_PERFECT,
            'type' => $type,
        ]);

        $coding = $codePrefix . $model->id;

        $this->assertSame(
            $externalReference,
            $this->app->make(ExternalReferencesService::class)->getReferenceByCoding($coding)
        );
    }

    public function codePrefixDataProvider(): array
    {
        return [
            ['GC:1:ORDER:', Order::class, ExternalReferenceType::ORDER],
            ['GC:1:ORDER:DCC:', Order::class, ExternalReferenceType::DCC],
            ['GC:1:ORDER:SHIP:', Order::class, ExternalReferenceType::SHIPPING],
            ['GC:1:ORDER:TAX:', Order::class, ExternalReferenceType::TAX],
            ['GC:1:ITEM:', OrderItem::class, ExternalReferenceType::ITEM],
            ['GC:1:ITEM:DCC:', OrderItem::class, ExternalReferenceType::DCC],
            ['GC:1:ITEM:PLEDGE:', OrderItem::class, ExternalReferenceType::PLEDGE],
            ['GC:1:TXN:', Transaction::class, ExternalReferenceType::TXN],
            ['GC:1:TXN:TXNSPLIT:', Transaction::class, ExternalReferenceType::TXNSPLIT],
            ['GC:1:TXN:DCC:', Transaction::class, ExternalReferenceType::DCC],
            ['GC:1:TXN:SHIP:', Transaction::class, ExternalReferenceType::SHIPPING],
            ['GC:1:TXN:TAX:', Transaction::class, ExternalReferenceType::TAX],
        ];
    }

    /**
     * @dataProvider unknownCodeDataProvider
     */
    public function testReturnsNullOnUnmatchedCoding(string $code): void
    {
        $this->assertNull($this->app->make(ExternalReferencesService::class)->getReferenceByCoding($code));
    }

    public function unknownCodeDataProvider(): array
    {
        return [
            ['fishy_value'],
            ['GC:NOTHING:12'],
            ['GC:DCC:NON_NUMERIC'],
        ];
    }
}
