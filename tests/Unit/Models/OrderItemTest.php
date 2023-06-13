<?php

namespace Tests\Unit\Models;

use Ds\Domain\Sponsorship\Events\SponsorWasStarted;
use Ds\Domain\Sponsorship\Models\Sponsorship;
use Ds\Enums\RecurringFrequency;
use Ds\Models\FundraisingPage;
use Ds\Models\Member;
use Ds\Models\Metadata;
use Ds\Models\Order;
use Ds\Models\OrderItem;
use Ds\Models\Product;
use Ds\Models\Variant;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class OrderItemTest extends TestCase
{
    public function testDesignationAccessorForSupportersChoice(): void
    {
        $item = OrderItem::factory()
            ->for(Order::factory())
            ->for(Variant::factory()->for(
                Product::factory()->designationOptionsForSupportersChoice()
            ))->create();

        $this->assertSame('General Fund', $item->designation);
    }

    public function testDesignationAccessorForSingleAccount(): void
    {
        $item = OrderItem::factory()
            ->for(Order::factory())
            ->for(Variant::factory()->for(
                Product::factory()->designationOptionsForSingleAccount()
            ))->create();

        $this->assertNull($item->designation);
    }

    public function testDesignationAccessorWithNoDesignationOptions(): void
    {
        $item = OrderItem::factory()
            ->for(Order::factory())
            ->for(Variant::factory()->for(Product::factory()))
            ->create();

        $this->assertNull($item->designation);
    }

    public function testCodeForVariantWithSku()
    {
        $product = Product::factory()->create();
        $variant = Variant::factory()->sku()->create();
        $product->variants()->save($variant);

        $orderItem = Order::factory()->create()->addItem([
            'variant_id' => $variant->getKey(),
        ]);

        $this->assertSame($variant->sku, $orderItem->code);
    }

    public function testCodeForVariantWithBlankSku()
    {
        $product = Product::factory()->create();
        $variant = Variant::factory()->create(['sku' => '']);
        $product->variants()->save($variant);

        $orderItem = Order::factory()->create()->addItem([
            'variant_id' => $variant->getKey(),
        ]);

        $this->assertSame($product->code, $orderItem->code);
    }

    public function testReceiptableAmountWithQtyGreaterThanOneAndFixedPriceVariantWithFairMarketValue()
    {
        $product = Product::factory()->allowOutOfStock()->receiptable()->create();
        $variant = Variant::factory()->create(['price' => 75, 'fair_market_value' => 40]);
        $product->variants()->save($variant);

        $orderItem = Order::factory()->create()->addItem([
            'variant_id' => $variant->getKey(),
            'qty' => 2,
        ]);

        $this->assertSame(70.0, $orderItem->receiptable_amount);
    }

    public function testFreeShippingOnVariants()
    {
        Product::factory()->create()->variants()->saveMany([
            $variantWithFreeShipping = Variant::factory()->freeShipping()->create(),
            $variantWithoutFreeShipping = Variant::factory()->create(),
        ]);

        $order = Order::factory()->create();

        $orderItemWithFreeShipping = $order->addItem(['variant_id' => $variantWithFreeShipping->getKey()]);
        $orderItemWithoutFreeShipping = $order->addItem(['variant_id' => $variantWithoutFreeShipping->getKey()]);

        $this->assertTrue($orderItemWithFreeShipping->has_free_shipping);
        $this->assertFalse($orderItemWithoutFreeShipping->has_free_shipping);
    }

    public function testShippingOnLockedItems()
    {
        Product::factory()->allowOutOfStock()->create()->variants()->saveMany([
            $variant = Variant::factory()->create(),
            $variantBundle = Variant::factory()->create(),
        ]);

        $variantBundle->linkedVariants()->attach($variant->getKey(), [
            'qty' => 10,
            'price' => 0,
        ]);

        $orderItem = Order::factory()->create()->addItem([
            'variant_id' => $variantBundle->getKey(),
        ]);

        foreach ($orderItem->lockedItems as $lockedItem) {
            $this->assertTrue($lockedItem->requires_shipping);
        }

        sys_set(['shipping_linked_items' => 'bundle']);

        foreach ($orderItem->lockedItems as $lockedItem) {
            $this->assertFalse($lockedItem->requires_shipping);
        }
    }

    /**
     * @dataProvider createSponsorSourceDataProvider
     */
    public function testCreateSponsorWillNotifySupporterIfSourceIsNotImport(string $source, bool $expected): void
    {
        Event::fake();

        $orderItem = OrderItem::factory()
            ->for(Sponsorship::factory())
            ->for(Order::factory()->has(Member::factory()))->create();

        $orderItem->createSponsor($source);

        Event::assertDispatched(function (SponsorWasStarted $event) use ($expected) {
            return $event->option('do_not_send_email') === $expected;
        });
    }

    public function createSponsorSourceDataProvider(): array
    {
        return [
            ['Website', false],
            ['Import', true],
            ['Another value', false],
        ];
    }

    public function testProductLevelGlCodeFromMeta1(): void
    {
        $item = OrderItem::factory()
            ->for(Order::factory())
            ->for(Variant::factory()->for(
                Product::factory()->state(['meta1' => 'GENERAL_FUND'])
            ))->create();

        $this->assertSame('GENERAL_FUND', $item->gl_code);
    }

    public function testProductLevelGlCodeFromDesignationOptionsDefault(): void
    {
        $item = OrderItem::factory()
            ->for(Order::factory())
            ->for(Variant::factory()->for(
                Product::factory()
                    ->designationOptionsForSingleAccount()
                    ->state(['meta1' => 'BUILDING_FUND'])
            ))->create();

        $this->assertSame('GENERAL_FUND', $item->gl_code);
    }

    public function testVariantLevelDpGlCodeOverrides(): void
    {
        $item = OrderItem::factory()
            ->for(Order::factory())
            ->for(
                Variant::factory()
                    ->for(Product::factory()->designationOptionsForSingleAccount())
                    ->has(
                        Metadata::factory()->state([
                            'key' => 'dp_gl_code',
                            'value' => 'BUILDING_FUND',
                        ]),
                        'metadataRelation'
                    )
            )->create();

        $this->assertSame('BUILDING_FUND', $item->gl_code);
    }

    public function testSponsorshipLevelGlCode(): void
    {
        $item = OrderItem::factory()
            ->for(Order::factory())
            ->for(Sponsorship::factory()->state(['meta1' => 'GENERAL_FUND']))
            ->create();

        $this->assertSame('GENERAL_FUND', $item->gl_code);
    }

    public function testItemLevelDpGlCodeOverrides(): void
    {
        $item = OrderItem::factory()
            ->for(Order::factory())
            ->for(Sponsorship::factory()->state(['meta1' => 'GENERAL_FUND']))
            ->has(
                Metadata::factory()->state([
                    'key' => 'dp_gl_code',
                    'value' => 'BUILDING_FUND',
                ]),
                'metadataRelation'
            )->create();

        // must reload as OrderItemObserver loads the relation before
        // the factory adds the metadata using the has method
        $item->load('metadataRelation');

        $this->assertSame('BUILDING_FUND', $item->gl_code);
    }

    public function testItemLevelGlCodeFromGeneralLedgerCode(): void
    {
        $item = OrderItem::factory()
            ->for(Order::factory())
            ->for(Sponsorship::factory()->state(['meta1' => 'GENERAL_FUND']))
            ->create(['general_ledger_code' => 'BUILDING_FUND']);

        $this->assertSame('BUILDING_FUND', $item->gl_code);
    }

    /**
     * @dataProvider glCodeMutatorSetsGeneralLedgerCodeProvider
     */
    public function testGlCodeMutatorSetsGeneralLedgerCode(string $expected, ?string $value): void
    {
        $item = OrderItem::factory()
            ->for(Order::factory())
            ->for(Variant::factory()->for(
                Product::factory()->state(['meta1' => 'GENERAL_FUND'])
            ))->create();

        $item->gl_code = $value;

        $this->assertSame($expected, $item->general_ledger_code);
    }

    public function glCodeMutatorSetsGeneralLedgerCodeProvider(): array
    {
        return [
            ['GENERAL_FUND', null],
            ['BUILDING_FUND', 'BUILDING_FUND'],
        ];
    }

    /**
     * @dataProvider adminLinkForFundraisingPageProvider
     */
    public function testAdminLinkForFundraisingPage(?string $permission, ?string $expecting): void
    {
        $item = OrderItem::factory()
            ->for(Order::factory())
            ->for(FundraisingPage::factory(), 'fundraisingPage')
            ->create();

        $this->actingAsUser()->withUserPermissions($permission);

        $this->assertSame(
            str_replace('{id}', $item->fundraisingPage->getKey(), $expecting) ?: null,
            $item->admin_link
        );
    }

    public function adminLinkForFundraisingPageProvider(): array
    {
        return [
            [null, null],
            ['fundraisingpages.edit', 'https://testing.givecloud.test/jpanel/fundraising-pages/{id}'],
        ];
    }

    /**
     * @dataProvider adminLinkForProductProvider
     */
    public function testAdminLinkForProduct(?string $permission, ?string $expecting): void
    {
        $item = OrderItem::factory()
            ->for(Order::factory())
            ->for(Variant::factory()->for(Product::factory()))
            ->create();

        $this->actingAsUser()->withUserPermissions($permission);

        $this->assertSame(
            str_replace('{id}', $item->variant->product->getKey(), $expecting) ?: null,
            $item->admin_link
        );
    }

    public function adminLinkForProductProvider(): array
    {
        return [
            [null, null],
            ['product.view', 'https://testing.givecloud.test/jpanel/products/edit?i={id}'],
        ];
    }

    /**
     * @dataProvider adminLinkForSponsorshipProvider
     */
    public function testAdminLinkForSponsorship(?string $permission, ?string $expecting): void
    {
        $item = OrderItem::factory()
            ->for(Order::factory())
            ->for(Sponsorship::factory())
            ->create();

        $this->actingAsUser()->withUserPermissions($permission);

        $this->assertSame(
            str_replace('{id}', $item->sponsorship->getKey(), $expecting) ?: null,
            $item->admin_link
        );
    }

    public function adminLinkForSponsorshipProvider(): array
    {
        return [
            [null, null],
            ['sponsorship.view', 'https://testing.givecloud.test/jpanel/sponsorship/{id}'],
        ];
    }

    /** @dataProvider localizedRecurringDescriptionDataProvider */
    public function testLocalizedPaymentStringIsLocalized(string $locale, ?string $frequency, string $expected): void
    {
        $this->app->setLocale($locale);

        $item = OrderItem::factory()->state([
            'price' => '19.99',
            'recurring_amount' => '19.99',
            'recurring_frequency' => $frequency,
            'recurring_day' => 1,
            'recurring_day_of_week' => 2,
        ])->for(Order::factory()->create(['confirmationdatetime' => '2020-10-15']))
            ->create();

        $this->assertSame($expected, $item->payment_string);
    }

    public function localizedRecurringDescriptionDataProvider(): array
    {
        return [
            ['en-CA', null, '$19.99 USD one time'],
            ['en-CA', RecurringFrequency::WEEKLY, '$19.99 USD/wk starting Wednesday, Oct 21st, 2020'],
            ['en-CA', RecurringFrequency::BIWEEKLY, '$19.99 USD/2wk starting Wednesday, Oct 28th, 2020'],
            ['en-CA', RecurringFrequency::MONTHLY, '$19.99 USD/mth starting Nov 14th, 2020'],
            ['en-CA', RecurringFrequency::QUARTERLY, '$19.99 USD/qr starting Jan 14th, 2021'],
            ['en-CA', RecurringFrequency::BIANNUALLY, '$19.99 USD/6mth starting Apr 14th, 2021'],
            ['en-CA', RecurringFrequency::ANNUALLY, '$19.99 USD/yr starting Oct 14th, 2021'],

            ['fr-CA', null, '$19,99 USD une fois'],
            ['fr-CA', RecurringFrequency::WEEKLY, '$19,99 USD/sem débutant le mercredi 21 oct. 2020'],
            ['fr-CA', RecurringFrequency::BIWEEKLY, '$19,99 USD/2sem débutant le mercredi 28 oct. 2020'],
            ['fr-CA', RecurringFrequency::MONTHLY, '$19,99 USD/mois débutant le 14 nov. 2020'],
            ['fr-CA', RecurringFrequency::QUARTERLY, '$19,99 USD/tri débutant le 14 janv. 2021'],
            ['fr-CA', RecurringFrequency::BIANNUALLY, '$19,99 USD/6mois débutant le 14 avr. 2021'],
            ['fr-CA', RecurringFrequency::ANNUALLY, '$19,99 USD/an débutant le 14 oct. 2021'],

            ['es-MX', null, '$19.99 USD una vez'],
            ['es-MX', RecurringFrequency::WEEKLY, '$19.99 USD/sem a partir de miércoles 21 de oct. 2020'],
            ['es-MX', RecurringFrequency::BIWEEKLY, '$19.99 USD/2sem a partir de miércoles 28 de oct. 2020'],
            ['es-MX', RecurringFrequency::MONTHLY, '$19.99 USD/mes a partir de 14 de nov. 2020'],
            ['es-MX', RecurringFrequency::QUARTERLY, '$19.99 USD/cuarto a partir de 14 de ene. 2021'],
            ['es-MX', RecurringFrequency::BIANNUALLY, '$19.99 USD/6mes a partir de 14 de abr. 2021'],
            ['es-MX', RecurringFrequency::ANNUALLY, '$19.99 USD/año a partir de 14 de oct. 2021'],
        ];
    }

    public function testIsDownloadableReturnsFalseWhenNoFile(): void
    {
        $orderItem = OrderItem::factory()->for(Order::factory())->create();

        $this->assertFalse($orderItem->is_downloadable);
    }

    public function testIsDownloadableReturnsTrueWhenFile(): void
    {
        $orderItem = OrderItem::factory()->for(Order::factory())->hasFile()->create();

        $this->assertTrue($orderItem->is_downloadable);
    }

    public function testDownloadLinkAccessorReturnsNullWhenNoFile(): void
    {
        $orderItem = OrderItem::factory()->for(Order::factory())->create();

        $this->assertNull($orderItem->download_link);
    }

    public function testDownloadLinkAccessorReturnsUrlOfFile(): void
    {
        $orderItem = OrderItem::factory()->for(Order::factory())->hasFile()->create();

        $this->assertStringContainsString('https://testing.givecloud.test/ds/file?o=', $orderItem->download_link);
    }

    public function testNotifyParamsReturnsMembershipTags(): void
    {
        /** @var \Ds\Models\OrderItem $orderItem */
        $orderItem = OrderItem::factory()
            ->for(Order::factory())
            ->for(Variant::factory()->for(Product::factory()))
            ->hasGroupAccount()
            ->create();

        $params = $orderItem->notifyParams();

        $this->assertSame(fromUtcFormat($orderItem->groupAccount->groupAccountTimespan->end_date, 'F d, Y'), $params['membership_expiry_date']);
        $this->assertSame($orderItem->groupAccount->group->name, $params['membership_name']);
        $this->assertSame($orderItem->groupAccount->group->description, $params['membership_description']);
        $this->assertNull($params['membership_renewal_url']);
    }
}
