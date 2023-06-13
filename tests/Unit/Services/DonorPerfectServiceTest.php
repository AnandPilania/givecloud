<?php

namespace Tests\Unit\Services;

use Closure;
use Ds\Domain\Commerce\Models\PaymentProvider;
use Ds\Enums\ExternalReference\ExternalReferenceType;
use Ds\Models\Member;
use Ds\Models\Order;
use Ds\Models\OrderItem;
use Ds\Models\Product;
use Ds\Models\RecurringPaymentProfile;
use Ds\Models\Transaction;
use Ds\Models\Variant;
use Ds\Services\DonorPerfectService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Tests\Concerns\InteractsWithRpps;
use Tests\StoryBuilder;
use Tests\TestCase;

class DonorPerfectServiceTest extends TestCase
{
    use InteractsWithRpps;

    /**
     * @dataProvider shouldUpdateDonorMembershipProvider
     */
    public function testShouldUpdateDonorMembership(bool $expected, ?int $dpId): void
    {
        $contribution = StoryBuilder::onetimeContribution()
            ->forDpMembership($dpId)
            ->create();

        $this->assertSame(
            $expected,
            $this->app->make(DonorPerfectService::class)->shouldUpdateDonorMembership($contribution)
        );
    }

    public function shouldUpdateDonorMembershipProvider(): array
    {
        return [
            [true, 12],
            [false, null],
        ];
    }

    /**
     * @dataProvider fairMarketValueItemMetadataOverrideProvider
     */
    public function testFairMarketValueItemMetadataOverride(?string $dpFairMarketValue): void
    {
        $item = OrderItem::factory()
            ->for(Order::factory())
            ->create();

        $item->metadata(['dp_fair_market_value' => $dpFairMarketValue]);
        $item->save();

        $data = [];
        $this->app->make(DonorPerfectService::class)->applyItemMetadataOverrides($data, $item);

        $this->assertArrayHasKey('fmv', $data);
        $this->assertSame(
            $dpFairMarketValue === 'Y' ? $item->total : null,
            $data['fmv']
        );
    }

    public function fairMarketValueItemMetadataOverrideProvider(): array
    {
        return [
            ['Y'],
            ['N'],
        ];
    }

    public function testGiftExistsReturnsTrue(): void
    {
        /** @var \Ds\Services\DonorPerfectService */
        $donorPerfectService = $this->createPartialMock(DonorPerfectService::class, ['giftExists']);
        $donorPerfectService
            ->expects($this->once())
            ->method('giftExists')
            ->willReturn(true);

        $this->assertTrue($donorPerfectService->giftExists(12));
    }

    public function testGiftExistsReturnsFalseIfNotFound(): void
    {
        /** @var \Ds\Services\DonorPerfectService */
        $donorPerfectService = $this->createPartialMock(DonorPerfectService::class, ['giftExists']);
        $donorPerfectService
            ->expects($this->once())
            ->method('giftExists')
            ->willReturn(false);

        $this->assertFalse($donorPerfectService->giftExists(12));
    }

    public function testCreateOrUpdateGiftCreatesGiftIfNoReference(): void
    {
        /** @var \Ds\Services\DonorPerfectService */
        $donorPerfectService = $this->createPartialMock(DonorPerfectService::class, ['createNewGift']);
        $donorPerfectService
            ->expects($this->once())
            ->method('createNewGift')
            ->willReturn(1);

        $this->assertSame(1, $donorPerfectService->createOrUpdateGift(['key' => 1]));
    }

    public function testCreateOrUpdateGiftCreatesGiftIfReferenceNotFound(): void
    {
        /** @var \Ds\Services\DonorPerfectService */
        $donorPerfectService = $this->createPartialMock(DonorPerfectService::class, ['createNewGift', 'getGiftIdByReference']);
        $donorPerfectService
            ->expects($this->once())
            ->method('createNewGift')
            ->willReturn(1);

        $this->assertSame(1, $donorPerfectService->createOrUpdateGift(['gc_reference' => 'inexisting']));
    }

    public function testNewGiftReturnsArrayWithIntegerGiftId(): void
    {
        /** @var \Ds\Services\DonorPerfectService */
        $donorPerfectService = $this->createPartialMock(DonorPerfectService::class, ['procedure', 'updateCalculatedFields']);
        $donorPerfectService
            ->expects($this->once())
            ->method('procedure')
            ->willReturn('121');

        $giftData = $donorPerfectService->newGift([]);

        $this->assertIsInt($giftData['gift_id']);
    }

    public function testGetGiftIdByReferenceWillReturnId(): void
    {
        $gift = (object) ['gift_id' => 123];

        /** @var \Ds\Services\DonorPerfectService */
        $donorPerfectService = $this->createPartialMock(DonorPerfectService::class, ['getGiftByReference']);
        $donorPerfectService
            ->expects($this->once())
            ->method('getGiftByReference')
            ->willReturn($gift);

        $giftId = $donorPerfectService->getGiftIdByReference('some_reference');
        $this->assertSame(123, $giftId);
    }

    public function testGetGiftByReferenceWillReturnGift(): void
    {
        $gift = (object) ['gift_id' => 123];

        /** @var \Ds\Services\DonorPerfectService */
        $donorPerfectService = $this->createPartialMock(DonorPerfectService::class, ['getGiftByReference']);
        $donorPerfectService
            ->expects($this->once())
            ->method('getGiftByReference')
            ->willReturn($gift);

        $this->assertSame($gift, $donorPerfectService->getGiftByReference('some_reference'));
    }

    /**
     * @dataProvider referenceCodingDataProvider
     */
    public function testGetReferenceCodingReturnsCoding(string $expected, string $object, ?string $type = null): void
    {
        $model = (new $object)->factory()->make(['id' => 12]);

        $this->assertSame($expected, $this->app->make(DonorPerfectService::class)->getReferenceCoding($model, $type));
    }

    public function referenceCodingDataProvider(): array
    {
        return [
            ['GC:1:ORDER:12', Order::class],
            ['GC:1:ORDER:DCC:12', Order::class, ExternalReferenceType::DCC],
            ['GC:1:ORDER:SHIP:12', Order::class, ExternalReferenceType::SHIPPING],
            ['GC:1:ORDER:TAX:12', Order::class, ExternalReferenceType::TAX],
            ['GC:1:ITEM:12', OrderItem::class],
            ['GC:1:ITEM:DCC:12', OrderItem::class, ExternalReferenceType::DCC],
            ['GC:1:ITEM:SHIP:12', OrderItem::class, ExternalReferenceType::SHIPPING],
            ['GC:1:ITEM:TAX:12', OrderItem::class, ExternalReferenceType::TAX],
            ['GC:1:TXN:12', Transaction::class],
            ['GC:1:TXN:TXNSPLIT:12', Transaction::class, ExternalReferenceType::TXNSPLIT],
            ['GC:1:TXN:DCC:12', Transaction::class, ExternalReferenceType::DCC],
            ['GC:1:TXN:SHIP:12', Transaction::class, ExternalReferenceType::SHIPPING],
            ['GC:1:TXN:TAX:12', Transaction::class, ExternalReferenceType::TAX],
        ];
    }

    public function testCalculatedFieldsAreNotTriggeredWhenSettingIsDisabled(): void
    {
        /** @var \Ds\Services\DonorPerfectService $service */
        $service = $this->partialMock(DonorPerfectService::class);
        $service->shouldNotHaveReceived('getCalculatedFields');

        sys_set('dp_trigger_calculated_fields', 0);

        $service->updateCalculatedFields(1, null);
    }

    public function testCalculatedFieldsAreTriggeredWhenSettingIsEnabled(): void
    {
        /** @var \Ds\Services\DonorPerfectService $service */
        $service = $this->partialMock(DonorPerfectService::class);
        $service->shouldReceive('getCalculatedFields')->once()->andReturn(collect([]));

        sys_set('dp_trigger_calculated_fields', 1);

        $service->updateCalculatedFields(1, null);
    }

    public function testCalculatedFieldsAreTriggeredIndividuallyWhenSettingIsEnabled(): void
    {
        $fields = [
            (object) ['calc_field_id' => 1],
            (object) ['calc_field_id' => 2],
        ];

        /** @var \Ds\Services\DonorPerfectService $service */
        $service = $this->partialMock(DonorPerfectService::class);
        $service->shouldReceive('getCalculatedFields')->once()->andReturn(collect($fields));
        $service->shouldReceive('updateCalculatedField')->twice()->andReturn();

        sys_set('dp_trigger_calculated_fields', 1);

        $service->updateCalculatedFields(1, null);
    }

    public function testPushPaymentMethodFromOrderWhenNotUsingDpForRecurring(): void
    {
        sys_set(['rpp_donorperfect' => 0]);

        $order = Order::factory()
            ->for(PaymentProvider::factory()->safesave())
            ->has(OrderItem::factory()->recurring('monthly')->count(1), 'items')
            ->create([
                'vault_id' => Str::random(12),
            ])->calculate();

        $dpService = $this->app->make(DonorPerfectService::class);
        $this->assertNull($dpService->pushPaymentMethodFromOrder(1, $order));
    }

    public function testPushPaymentMethodFromOrderWithoutVaultId(): void
    {
        sys_set(['rpp_donorperfect' => 1]);

        $order = Order::factory()
            ->for(PaymentProvider::factory()->safesave())
            ->has(OrderItem::factory()->recurring('monthly')->count(1), 'items')
            ->create()
            ->calculate();

        $dpService = $this->app->make(DonorPerfectService::class);
        $this->assertNull($dpService->pushPaymentMethodFromOrder(1, $order));
    }

    public function testPushPaymentMethodFromOrderWithoutRecurringItems(): void
    {
        sys_set(['rpp_donorperfect' => 1]);

        $order = Order::factory()
            ->for(PaymentProvider::factory()->safesave())
            ->has(OrderItem::factory()->count(1), 'items')
            ->create([
                'vault_id' => Str::random(12),
            ])->calculate();

        $dpService = $this->app->make(DonorPerfectService::class);
        $this->assertNull($dpService->pushPaymentMethodFromOrder(1, $order));
    }

    public function testPushPaymentMethodFromOrderWithNonSafeSavePaymentProvider(): void
    {
        sys_set(['rpp_donorperfect' => 1]);

        $order = Order::factory()
            ->for(PaymentProvider::factory()->paypalExpress())
            ->has(OrderItem::factory()->recurring('monthly')->count(1), 'items')
            ->create([
                'vault_id' => Str::random(12),
            ])->calculate();

        $dpService = $this->app->make(DonorPerfectService::class);
        $this->assertNull($dpService->pushPaymentMethodFromOrder(1, $order));
    }

    public function testPushPaymentMethodFromOrderWithSafeSavePaymentProvider(): void
    {
        sys_set(['rpp_donorperfect' => 1]);

        $order = Order::factory()
            ->for(PaymentProvider::factory()->safesave())
            ->has(OrderItem::factory()->recurring('monthly')->count(1), 'items')
            ->create([
                'vault_id' => Str::random(12),
            ])->calculate();

        $dpService = $this->createPartialMock(DonorPerfectService::class, ['findOrNewPaymentMethod']);

        $dpService->expects($this->once())
            ->method('findOrNewPaymentMethod')
            ->willReturn([
                'id' => $paymentMethodId = mt_rand(1, 100),
            ]);

        $data = $dpService->pushPaymentMethodFromOrder(1, $order);

        $this->assertArrayHasKey('id', $data);
        $this->assertSame($data['id'], $paymentMethodId);
    }

    public function testFindOrNewPaymentMethodWithExistingDpPaymentMethod(): void
    {
        $dpService = $this->createPartialMock(DonorPerfectService::class, ['findPaymentMethod']);

        $dpService->expects($this->once())
            ->method('findPaymentMethod')
            ->willReturn((object) [
                'dppaymentmethodid' => $paymentMethodId = mt_rand(1, 100),
            ]);

        $data = $dpService->findOrNewPaymentMethod([
            'donor_id' => 1,
            'vault_id' => Str::random(12),
        ]);

        $this->assertArrayHasKey('id', $data);
        $this->assertSame($data['id'], $paymentMethodId);
    }

    public function testFindOrNewPaymentMethodWithNewDpPaymentMethod(): void
    {
        $dpService = $this->createPartialMock(DonorPerfectService::class, ['findPaymentMethod', 'procedure']);

        $dpService->expects($this->once())
            ->method('findPaymentMethod')
            ->willReturn(null);

        $dpService->expects($this->once())
            ->method('procedure')
            ->willReturn([
                (object) ['dppaymentmethodid' => $paymentMethodId = mt_rand(1, 100)],
            ]);

        $data = $dpService->findOrNewPaymentMethod([
            'donor_id' => 1,
            'vault_id' => Str::random(12),
        ]);

        $this->assertArrayHasKey('id', $data);
        $this->assertSame($data['id'], $paymentMethodId);
    }

    /**
     * @dataProvider checkingIfSplitGiftsShouldBeUsedForAnOrderProvider
     */
    public function testCheckingIfSplitGiftsShouldBeUsedForAnOrder(
        bool $splitGiftsShouldBeUsed,
        bool $enableSplitGifts,
        int $itemCount,
        bool $enableDccAsSeparateGift,
        float $dccAmount,
        float $shippingAmount,
        float $taxAmount
    ): void {
        sys_set([
            'dp_enable_split_gifts' => $enableSplitGifts,
            'dp_dcc_is_separate_gift' => $enableDccAsSeparateGift,
        ]);

        $order = Order::factory()
            ->has(OrderItem::factory()->count($itemCount), 'items')
            ->create([
                'dcc_total_amount' => $dccAmount,
                'shipping_amount' => $shippingAmount,
                'taxtotal' => $taxAmount,
            ]);

        $this->assertSame(
            $splitGiftsShouldBeUsed,
            $this->app->make(DonorPerfectService::class)->shouldEnableSplitGiftsForOrder($order)
        );
    }

    public function checkingIfSplitGiftsShouldBeUsedForAnOrderProvider(): array
    {
        return [
            [false, false, 1, false, 0, 0, 0],
            [false, true, 1, false, 0, 0, 0],
            [true, true, 1, true, 10, 0, 0],
            [false, true, 1, false, 10, 0, 0],
            [true, true, 1, false, 0, 10, 0],
            [true, true, 1, false, 0, 0, 10],
            [true, true, 2, false, 0, 0, 0],
        ];
    }

    /**
     * @dataProvider checkingIfSplitGiftsShouldBeUsedForATransactionProvider
     */
    public function testCheckingIfSplitGiftsShouldBeUsedForATransaction(
        bool $splitGiftsShouldBeUsed,
        bool $enableSplitGifts,
        bool $enableDccAsSeparateGift,
        float $dccAmount,
        float $shippingAmount,
        float $taxAmount
    ): void {
        sys_set([
            'dp_enable_split_gifts' => $enableSplitGifts,
            'dp_dcc_is_separate_gift' => $enableDccAsSeparateGift,
        ]);

        $transaction = Transaction::factory()
            ->create([
                'dcc_amount' => $dccAmount,
                'shipping_amt' => $shippingAmount,
                'tax_amt' => $taxAmount,
            ]);

        $this->assertSame(
            $splitGiftsShouldBeUsed,
            $this->app->make(DonorPerfectService::class)->shouldEnableSplitGiftsForTransaction($transaction)
        );
    }

    public function checkingIfSplitGiftsShouldBeUsedForATransactionProvider(): array
    {
        return [
            [false, false, false, 0, 0, 0],
            [false, true, false, 0, 0, 0],
            [true, true, true, 10, 0, 0],
            [false, true, false, 10, 0, 0],
            [true, true, false, 0, 10, 0],
            [true, true, false, 0, 0, 10],
        ];
    }

    public function testProductCodingAppliedToMetaUdfsForTransactions(): void
    {
        $this->assertMetaUdfsForTransactionMeta21Value('Darth Vader');
    }

    public function testVariantCodingAppliedToMetaUdfsForTransactions(): void
    {
        $this->assertMetaUdfsForTransactionMeta21Value('Luke Skywalker', function (Variant $variant) {
            $variant->metadata(['meta21' => 'Luke Skywalker']);
            $variant->save();
        });
    }

    private function assertMetaUdfsForTransactionMeta21Value(string $expecting, Closure $callback = null): void
    {
        sys_set([
            'dp_meta21_field' => 'udf_1',
            'dp_meta21_label' => 'UDF 1',
        ]);

        $variant = Variant::factory()
            ->donation()
            ->for(
                Product::factory()
                    ->donation()
                    ->allowOutOfStock()
                    ->create(['meta21' => 'Darth Vader'])
            )->create();

        if ($callback) {
            $callback($variant);
        }

        $rpp = $this->generateRpps(
            $account = $this->generateAccountWithPaymentMethods(),
            $account->defaultPaymentMethod,
            1,
            'USD',
            function (Order $order) use ($variant) {
                $order->addItem([
                    'variant_id' => $variant->getKey(),
                    'amt' => 10,
                    'recurring_frequency' => 'monthly',
                    'recurring_day' => 1,
                ]);
            }
        )[0];

        $udfs = $this->app->make(DonorPerfectService::class)->metaUdfsForTransaction(
            Transaction::factory()->create(['recurring_payment_profile_id' => $rpp->getKey()])
        );

        $this->assertSame($expecting, $udfs['udf_1'] ?? null);
    }

    public function testPushOrderSetsAutoSyncToFalseWhenProductIsNotSyncable(): void
    {
        sys_set('dpo_api_key', Str::random(60));

        $key = 'dp-ping-' . $this->app->make('dpo.client')->getAuthFingerpint();
        Cache::set($key, true);

        $product = Product::factory()->create();
        $product->setMetadata('dp_syncable', false);
        $product->save();

        $order = Order::factory()->create();

        $order = OrderItem::factory()
            ->for($order)
            ->for(Variant::factory()->for($product))
            ->create()->order;

        $this->assertTrue($order->dp_sync_order);

        $this->app->make(DonorPerfectService::class)->pushOrder($order);

        $order->refresh();

        $this->assertFalse($order->dp_sync_order);
    }

    public function testPushTransactionDoesSetAutoSyncToFalseWhenProductIsNotSyncable(): void
    {
        sys_set('dpo_api_key', Str::random(60));

        $key = 'dp-ping-' . $this->app->make('dpo.client')->getAuthFingerpint();
        Cache::set($key, true);

        $product = Product::factory()->create();
        $product->setMetadata('dp_syncable', false);
        $product->save();

        $order = Order::factory()->create();

        $orderItem = OrderItem::factory()
            ->for($order)
            ->for(Variant::factory()->for($product));

        $rpp = RecurringPaymentProfile::factory()
            ->for($product)
            ->for($order)
            ->for(Member::factory())
            ->for($orderItem, 'order_item');

        $transaction = Transaction::factory()->for($rpp)->create();

        $this->assertTrue($transaction->dp_auto_sync);

        $this->app->make(DonorPerfectService::class)->pushTransaction($transaction);

        $transaction->refresh();

        $this->assertFalse($transaction->dp_auto_sync);
    }
}
