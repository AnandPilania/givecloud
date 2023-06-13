<?php

namespace Tests\Unit\Services;

use Ds\Domain\Shared\Exceptions\MessageException;
use Ds\Models\FundraisingPage;
use Ds\Models\Member;
use Ds\Models\Membership;
use Ds\Models\Order;
use Ds\Models\OrderItem;
use Ds\Models\Product;
use Ds\Models\ProductCustomField;
use Ds\Models\Variant;
use Tests\TestCase;

/**
 * @group services
 * @group order
 */
class OrderServiceAddItemTest extends TestCase
{
    private $currencyCode = 'USD';

    public function setUp(): void
    {
        parent::setUp();

        // Set same currency for system and fundraising page
        // to avoid calling Swap exchange rate facade.
        sys_set('dpo_currency', $this->currencyCode);
    }

    public function testAddItem(): void
    {
        $product = Product::factory()->create();
        $variant = Variant::factory()->create();
        $product->variants()->save($variant);

        $orderItem = Order::factory()->create()->addItem([
            'variant_id' => $variant->getKey(),
            'qty' => 1,
        ]);

        $this->assertInstanceOf(OrderItem::class, $orderItem);
    }

    public function testAddItemThrowsExceptionWhenOrderIsPaid(): void
    {
        $order = Order::factory()->create(['confirmationdatetime' => now()]);
        $product = Product::factory()->create();
        $variant = Variant::factory()->create();
        $product->variants()->save($variant);

        $this->expectException(MessageException::class);
        $this->expectExceptionMessage("Unable to add items after a contribution has been paid (Contribution: $order->client_uuid)");

        $newItemRequestData = array_merge(
            OrderItem::factory()->make(['productorderid' => $order->getKey()])->toArray(),
            ['variant_id' => $variant->getKey()]
        );

        $order->addItem($newItemRequestData);
    }

    public function testAddItemThrowsExceptionWhenDonationIsLessThanMinimum(): void
    {
        $minimumDonation = 100;

        $order = Order::factory()->create();
        $product = Product::factory()->create();
        $variant = Variant::factory()->create([
            'is_donation' => true,
            'price_minimum' => $minimumDonation,
        ]);
        $product->variants()->save($variant);

        $this->expectException(MessageException::class);
        $this->expectExceptionMessage("Must be greater than $$minimumDonation.00");

        $newItemRequestData = array_merge(
            OrderItem::factory()->make(['productorderid' => $order->getKey()])->toArray(),
            ['variant_id' => $variant->getKey()],
            ['amt' => $minimumDonation - 10]
        );

        $order->addItem($newItemRequestData);
    }

    public function testAddItemThrowsExceptionWhenVariantIsNotAvailable(): void
    {
        $availableVariantsQuantity = 1;

        $order = Order::factory()->create();
        $product = Product::factory()->create();
        $variant = Variant::factory()->create([
            'quantity' => 1,
        ]);
        $product->variants()->save($variant);

        $this->expectException(MessageException::class);
        $this->expectExceptionMessage('We have limited stocks for this item. Only 1 is available for purchase.');

        $newItemRequestData = array_merge(
            OrderItem::factory()->make(['productorderid' => $order->getKey()])->toArray(),
            ['variant_id' => $variant->getKey()],
            ['qty' => $availableVariantsQuantity + 1]
        );

        $orderItem = $order->addItem($newItemRequestData);

        $this->assertInstanceOf(OrderItem::class, $orderItem);
    }

    public function testAddItemThrowsExceptionWhenBuyingMultipleMembershipsWithDpoEnabled(): void
    {
        sys_set('dpo_api_key', 'dpo-api-key');

        $order = Order::factory()->create();

        // Order already has one item with a variant in a different membership.
        $orderItem = OrderItem::factory()->make();
        $order->items()->save($orderItem);
        $product = Product::factory()->create();
        $variant = Variant::factory()->create();
        $product->variants()->save($variant);
        $variant->orderItems()->save($orderItem);

        $membership = Membership::factory()->create();
        $membership->variants()->save($variant);
        $product = Product::factory()->create();
        $variant = Variant::factory()->create();
        $product->variants()->save($variant);
        $membership = Membership::factory()->create();
        $membership->variants()->save($variant);

        $this->expectException(MessageException::class);
        $this->expectExceptionMessage('You can only purchase one type of membership.');

        $newItemRequestData = array_merge(
            OrderItem::factory()->make(['productorderid' => $order->getKey()])->toArray(),
            ['variant_id' => $variant->getKey()],
            ['qty' => 1]
        );

        $orderItem = $order->addItem($newItemRequestData);

        $this->assertInstanceOf(OrderItem::class, $orderItem);
    }

    public function testAddItemDonation(): void
    {
        $product = Product::factory()->create();
        $variant = Variant::factory()->donation()->create([
            'price' => 10,
        ]);
        $product->variants()->save($variant);

        $orderItem = Order::factory()->create()->addItem([
            'variant_id' => $variant->getKey(),
            'amt' => 15,
            'qty' => 1,
        ]);

        $this->assertInstanceOf(OrderItem::class, $orderItem);
    }

    public function testAddItemRecurringDonationRppNatural(): void
    {
        sys_set('rpp_default_type', 'natural');

        $product = Product::factory()->create();
        $variant = Variant::factory()->donation()->recurring()->create([
            'price' => 10,
        ]);
        $product->variants()->save($variant);

        $orderItem = Order::factory()->create()->addItem([
            'variant_id' => $variant->getKey(),
            'amt' => 15,
            'qty' => 1,
            'recurring_frequency' => 'monthly',
        ]);

        $this->assertInstanceOf(OrderItem::class, $orderItem);
        $this->assertTrue($orderItem->recurring_with_initial_charge);
    }

    public function testAddItemRecurringDonationRppNaturalWithStartDate(): void
    {
        sys_set('rpp_default_type', 'natural');

        $product = Product::factory()->create();
        $variant = Variant::factory()->donation()->recurring()->create([
            'price' => 10,
            'billing_starts_on' => today(),
        ]);
        $product->variants()->save($variant);

        $orderItem = Order::factory()->create()->addItem([
            'variant_id' => $variant->getKey(),
            'amt' => 15,
            'qty' => 1,
            'recurring_frequency' => 'monthly',
        ]);

        $this->assertInstanceOf(OrderItem::class, $orderItem);
        $this->assertFalse($orderItem->recurring_with_initial_charge);
        $this->assertEquals($variant->billing_starts_on, $orderItem->recurring_starts_on);
    }

    public function testAddItemRecurringDonationRppNaturalWeekly(): void
    {
        sys_set('rpp_default_type', 'natural');

        $product = Product::factory()->create();
        $variant = Variant::factory()->donation()->recurring()->create([
            'price' => 10,
        ]);
        $product->variants()->save($variant);

        $orderItem = Order::factory()->create()->addItem([
            'variant_id' => $variant->getKey(),
            'amt' => 15,
            'qty' => 1,
            'recurring_frequency' => 'weekly',
        ]);

        $this->assertInstanceOf(OrderItem::class, $orderItem);
    }

    public function testAddItemRecurringDonationRppFixed(): void
    {
        sys_set('rpp_default_type', 'fixed');

        $product = Product::factory()->create();
        $variant = Variant::factory()->donation()->recurring()->create([
            'price' => 10,
        ]);
        $product->variants()->save($variant);

        $orderItem = Order::factory()->create()->addItem([
            'variant_id' => $variant->getKey(),
            'amt' => 15,
            'qty' => 1,
            'recurring_frequency' => 'monthly',
            'recurring_day' => 1,
            'recurring_with_initial_charge' => true,
        ]);

        $this->assertInstanceOf(OrderItem::class, $orderItem);
        $this->assertTrue($orderItem->recurring_with_initial_charge);
        $this->assertEquals(1, $orderItem->recurring_day);
    }

    public function testAddItemRecurringDonationRppFixedWithStartDate(): void
    {
        sys_set('rpp_default_type', 'fixed');

        $product = Product::factory()->create();
        $variant = Variant::factory()->donation()->recurring()->create([
            'price' => 10,
            'billing_starts_on' => today(),
        ]);
        $product->variants()->save($variant);

        $orderItem = Order::factory()->create()->addItem([
            'variant_id' => $variant->getKey(),
            'amt' => 15,
            'qty' => 1,
            'recurring_frequency' => 'monthly',
            'recurring_day' => 1,
        ]);

        $this->assertInstanceOf(OrderItem::class, $orderItem);
        $this->assertFalse($orderItem->recurring_with_initial_charge);
        $this->assertEquals($variant->billing_starts_on, $orderItem->recurring_starts_on);
        $this->assertEquals(1, $orderItem->recurring_day);
    }

    public function testAddItemRecurringDonationRppFixedWeekly(): void
    {
        sys_set('rpp_default_type', 'fixed');

        $product = Product::factory()->create();
        $variant = Variant::factory()->donation()->recurring()->create([
            'price' => 10,
        ]);
        $product->variants()->save($variant);

        $orderItem = Order::factory()->create()->addItem([
            'variant_id' => $variant->getKey(),
            'amt' => 15,
            'qty' => 1,
            'recurring_frequency' => 'weekly',
            'recurring_day_of_week' => 1,
        ]);

        $this->assertInstanceOf(OrderItem::class, $orderItem);
        $this->assertEquals(1, $orderItem->recurring_day_of_week);
    }

    public function testAddItemTributeEmail(): void
    {
        $product = Product::factory()->create();
        $variant = Variant::factory()->create();
        $product->variants()->save($variant);

        $fakeOrderItem = OrderItem::factory()->tributeEmail()->make();
        $requestData = [
            'is_tribute' => $fakeOrderItem->is_tribute,
            'dpo_tribute_id' => $fakeOrderItem->dpo_tribute_id,
            'tribute_type_id' => $fakeOrderItem->tribute_type_id,
            'tribute_name' => $fakeOrderItem->tribute_name,
            'tribute_notify' => $fakeOrderItem->tribute_notify,
            'tribute_notify_name' => $fakeOrderItem->tribute_notify_name,
            'tribute_notify_at' => $fakeOrderItem->tribute_notify_at,
            'tribute_message' => $fakeOrderItem->tribute_message,
            'tribute_notify_email' => $fakeOrderItem->tribute_notify_email,
        ];

        $orderItem = Order::factory()->create()->addItem(['variant_id' => $variant->getKey()] + $requestData);

        $this->assertInstanceOf(OrderItem::class, $orderItem);
        foreach ($requestData as $field => $value) {
            $this->assertEquals($orderItem->{$field}, $value);
        }
    }

    public function testAddItemTributeLetter(): void
    {
        $product = Product::factory()->create();
        $variant = Variant::factory()->create();
        $product->variants()->save($variant);

        $fakeOrderItem = OrderItem::factory()->tributeLetter()->make();
        $requestData = [
            'is_tribute' => $fakeOrderItem->is_tribute,
            'dpo_tribute_id' => $fakeOrderItem->dpo_tribute_id,
            'tribute_type_id' => $fakeOrderItem->tribute_type_id,
            'tribute_name' => $fakeOrderItem->tribute_name,
            'tribute_notify' => $fakeOrderItem->tribute_notify,
            'tribute_notify_name' => $fakeOrderItem->tribute_notify_name,
            'tribute_notify_at' => $fakeOrderItem->tribute_notify_at,
            'tribute_message' => $fakeOrderItem->tribute_message,
            'tribute_notify_address' => $fakeOrderItem->tribute_notify_address,
            'tribute_notify_city' => $fakeOrderItem->tribute_notify_city,
            'tribute_notify_state' => $fakeOrderItem->tribute_notify_state,
            'tribute_notify_zip' => $fakeOrderItem->tribute_notify_zip,
            'tribute_notify_country' => $fakeOrderItem->tribute_notify_country,
        ];

        $orderItem = Order::factory()->create()->addItem(['variant_id' => $variant->getKey()] + $requestData);

        $this->assertInstanceOf(OrderItem::class, $orderItem);
        foreach ($requestData as $field => $value) {
            $this->assertEquals($orderItem->{$field}, $value);
        }
    }

    public function testAddItemFundraisingPage(): void
    {
        $product = Product::factory()->create();
        $variant = Variant::factory()->create();
        $product->variants()->save($variant);

        $fakeFundraisingPage = FundraisingPage::factory()->create([
            'currency_code' => $this->currencyCode,
        ]);
        $requestData = [
            'fundraising_page_id' => $fakeFundraisingPage->getKey(),
            'fundraising_member_id' => $fakeFundraisingPage->memberOrganizer->getKey(),
        ];

        $orderItem = Order::factory()->create()->addItem(['variant_id' => $variant->getKey()] + $requestData);

        $this->assertInstanceOf(OrderItem::class, $orderItem);
        foreach ($requestData as $field => $value) {
            $this->assertEquals($orderItem->{$field}, $value);
        }
    }

    public function testAddItemWithVariantOnSale(): void
    {
        $product = Product::factory()->create();
        $variant = Variant::factory()->create([
            'price' => 20,
            'saleprice' => 10,
        ]);
        $product->variants()->save($variant);

        $orderItem = Order::factory()->create()->addItem(['variant_id' => $variant->getKey()]);

        $this->assertInstanceOf(OrderItem::class, $orderItem);
        $this->assertEquals($orderItem->discount, 10);
    }

    public function testAddItemWithMetadata(): void
    {
        $product = Product::factory()->create();
        $variant = Variant::factory()->create();
        $product->variants()->save($variant);

        $metadata = [
            'some' => 'testing',
            'metadata' => true,
        ];

        $orderItem = Order::factory()->create()->addItem([
            'variant_id' => $variant->getKey(),
            'metadata' => $metadata,
        ]);

        $this->assertInstanceOf(OrderItem::class, $orderItem);
        foreach ($metadata as $metaKey => $metaValue) {
            $this->assertSame($orderItem->getMetadata($metaKey)->getValue(), $metaValue);
        }
    }

    public function testAddItemWithLinkedVariants(): void
    {
        $product = Product::factory()->create();
        /** @var \Ds\Models\Variant */
        $variant = Variant::factory()->create();
        $product->variants()->save($variant);

        /** @var \Illuminate\Database\Eloquent\Collection */
        $linkedVariants = Variant::factory(3)->create();
        (Product::factory()->create())->variants()->saveMany($linkedVariants);
        $variant->linkedVariants()->saveMany($linkedVariants, ['price' => 3, 'qty' => 1]);

        $orderItem = Order::factory()->create()->addItem(['variant_id' => $variant->getKey(), 'qty' => 1]);

        $this->assertInstanceOf(OrderItem::class, $orderItem);
        $this->assertCount(3, $orderItem->lockedItems);
        foreach ($variant->linkedVariants as $linkedVariant) {
            $lockedItemsWithLinkedVariant = $orderItem->lockedItems
                ->filter(function ($lockedItem) use ($linkedVariant) {
                    return $lockedItem->variant->getKey() === $linkedVariant->getKey();
                });
            $this->assertCount(1, $lockedItemsWithLinkedVariant);

            $lockedItemsWithLinkedVariant = $lockedItemsWithLinkedVariant->first();
            $this->assertEquals($linkedVariant->pivot->price, $lockedItemsWithLinkedVariant->price);
            $this->assertEquals($linkedVariant->pivot->qty * 1, $lockedItemsWithLinkedVariant->qty);
            $this->assertEquals($orderItem->getKey(), $lockedItemsWithLinkedVariant->locked_to_item_id);
        }
    }

    public function testAddItemWithCustomFields(): void
    {
        $product = Product::factory()->create();
        $variant = Variant::factory()->create();
        $product->variants()->save($variant);

        $customFieldIds = ProductCustomField::factory(3)->create()->map->getKey()->toArray();
        $customFields = [
            $customFieldIds[0] => 'testing',
            $customFieldIds[1] => true,
            $customFieldIds[2] => ['id' => $customFieldIds[2], 'value' => 'value from array'],
        ];

        $orderItem = Order::factory()->create()->addItem([
            'variant_id' => $variant->getKey(),
            'fields' => $customFields,
        ]);

        $this->assertInstanceOf(OrderItem::class, $orderItem);
        foreach ($customFields as $fieldKey => $fieldValue) {
            $fieldsWithKey = $orderItem->fields->filter(function ($field) use ($fieldKey) {
                return $field->getKey() === $fieldKey;
            });

            $this->assertCount(1, $fieldsWithKey);
            $this->assertEquals(
                is_array($fieldValue) ? $fieldValue['value'] : $fieldValue,
                $fieldsWithKey->first()->value
            );
        }
    }

    public function testAddItemWithMember(): void
    {
        $order = Order::factory()->make([
            'member_id' => Member::factory()->create()->getKey(),
        ]);

        $product = Product::factory()->create();
        $variant = Variant::factory()->create();
        $product->variants()->save($variant);

        $orderItem = $order->addItem(['variant_id' => $variant->getKey()]);

        $this->assertInstanceOf(OrderItem::class, $orderItem);
    }
}
