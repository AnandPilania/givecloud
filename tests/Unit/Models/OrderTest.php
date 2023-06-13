<?php

namespace Tests\Unit\Models;

use Ds\Enums\RecurringFrequency;
use Ds\Models\Member as Account;
use Ds\Models\Order;
use Ds\Models\OrderItem;
use Ds\Models\Product;
use Ds\Models\RecurringPaymentProfile;
use Ds\Models\Variant;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class OrderTest extends TestCase
{
    public function testBothShippingForBundleWithFreeShipping()
    {
        sys_set(['shipping_linked_items' => 'both']);

        $single = Variant::factory()->create(['weight' => 2, 'is_shipping_free' => false, 'price' => 50]);
        $bundle = Variant::factory()->create(['weight' => 15, 'is_shipping_free' => true]);

        $product = Product::factory()->allowOutOfStock()->create();
        $product->variants()->saveMany([$single, $bundle]);

        $bundle->linkedVariants()->save($single, ['price' => 250, 'qty' => 10]);

        $order = Order::factory()->create();
        $order->populateMember(Account::factory()->create());

        $order->addItem(['variant_id' => $bundle->getKey(), 'qty' => 1]);

        $this->assertFalse($order->is_free_shipping, 'Has free shipping');
        $this->assertEquals(20, $order->total_weight, 'Has total weight');
        $this->assertEquals(2, $order->shippable_items, 'Has shippable items');
    }

    public function testBothShippingForBundleWithoutFreeShipping()
    {
        sys_set(['shipping_linked_items' => 'both']);

        $single = Variant::factory()->create(['weight' => 2, 'is_shipping_free' => false, 'price' => 50]);
        $bundle = Variant::factory()->create(['weight' => 15, 'is_shipping_free' => false]);

        $product = Product::factory()->allowOutOfStock()->create();
        $product->variants()->saveMany([$single, $bundle]);

        $bundle->linkedVariants()->save($single, ['price' => 250, 'qty' => 10]);

        $order = Order::factory()->create();
        $order->populateMember(Account::factory()->create());

        $order->addItem(['variant_id' => $bundle->getKey(), 'qty' => 1]);

        $this->assertFalse($order->is_free_shipping, 'Has free shipping');
        $this->assertEquals(35, $order->total_weight, 'Has total weight');
        $this->assertEquals(2, $order->shippable_items, 'Has shippable items');
    }

    public function testBundleShippingForBundleWithFreeShipping()
    {
        sys_set(['shipping_linked_items' => 'bundle']);

        $single = Variant::factory()->create(['weight' => 2, 'is_shipping_free' => false, 'price' => 50]);
        $bundle = Variant::factory()->create(['weight' => 15, 'is_shipping_free' => true]);

        $product = Product::factory()->allowOutOfStock()->create();
        $product->variants()->saveMany([$single, $bundle]);

        $bundle->linkedVariants()->save($single, ['price' => 250, 'qty' => 10]);

        $order = Order::factory()->create();
        $order->populateMember(Account::factory()->create());

        $order->addItem(['variant_id' => $bundle->getKey(), 'qty' => 1]);

        $this->assertTrue($order->is_free_shipping, 'Has free shipping');
        $this->assertEquals(0, $order->total_weight, 'Has total weight');
        $this->assertEquals(1, $order->shippable_items, 'Has shippable items');
    }

    public function testBundleShippingForBundleWithoutFreeShipping()
    {
        sys_set(['shipping_linked_items' => 'bundle']);

        $single = Variant::factory()->create(['weight' => 2, 'is_shipping_free' => false, 'price' => 50]);
        $bundle = Variant::factory()->create(['weight' => 15, 'is_shipping_free' => false]);

        $product = Product::factory()->allowOutOfStock()->create();
        $product->variants()->saveMany([$single, $bundle]);

        $bundle->linkedVariants()->save($single, ['price' => 250, 'qty' => 10]);

        $order = Order::factory()->create();
        $order->populateMember(Account::factory()->create());

        $order->addItem(['variant_id' => $bundle->getKey(), 'qty' => 1]);

        $this->assertFalse($order->is_free_shipping, 'Has free shipping');
        $this->assertEquals(15, $order->total_weight, 'Has total weight');
        $this->assertEquals(1, $order->shippable_items, 'Has shippable items');
    }

    /** @dataProvider paymentTypeDescriptionDataProvider */
    public function testPaymentTypeDescriptionIsLocalized(string $locale, string $paymentType, string $cardType, string $expected): void
    {
        $this->app->setLocale($locale);

        $order = Order::factory()->paid()->create([
            'payment_type' => $paymentType,
            'billingcardtype' => $cardType,
            'billingcardlastfour' => '1234',
            'confirmationnumber' => 'ABCD',
            'check_number' => 1,
            'check_date' => Carbon::parse('2021-10-14'),
            'payment_other_reference' => 'DEFG',
        ]);

        $this->assertSame($expected, $order->payment_type_description);
    }

    public function paymentTypeDescriptionDataProvider(): array
    {
        return [
            ['en-CA', 'check', '', 'Check (#1 dated Oct 14, 2021)'],
            ['en-CA', 'ach', 'business account', 'ACH (ending in 1234 - authorization ABCD)'],
            ['en-CA', 'ach', 'personal account', 'ACH (ending in 1234 - authorization ABCD)'],
            ['en-CA', 'card', 'visa', 'Visa (ending in 1234 - authorization ABCD)'],
            ['en-CA', 'card', 'vi', 'Visa (ending in 1234 - authorization ABCD)'],
            ['en-CA', 'card', 'amex', 'Amex (ending in 1234 - authorization ABCD)'],
            ['en-CA', 'card', 'mc', 'MasterCard (ending in 1234 - authorization ABCD)'],
            ['en-CA', 'card', 'discover', 'Discover (ending in 1234 - authorization ABCD)'],
            ['en-CA', 'vault', 'vault', 'Secure Account (ending in 1234 - authorization ABCD)'],
            ['en-CA', 'other', '', 'alternate payment (ref #DEFG)'],
            ['en-CA', 'paypal', 'paypal', 'PayPal account'],
            ['en-CA', 'cash', 'cash', 'Cash'],
            ['en-CA', 'free', 'free', 'Free'],

            ['fr-CA', 'check', '', 'Cheque (#1 en date du 14 oct., 2021)'],
            ['fr-CA', 'ach', 'business account', 'Compte (se terminant par 1234 - autorisation ABCD)'],
            ['fr-CA', 'ach', 'personal account', 'Compte (se terminant par 1234 - autorisation ABCD)'],
            ['fr-CA', 'card', 'visa', 'Visa (se terminant par 1234 - autorisation ABCD)'],
            ['fr-CA', 'card', 'vi', 'Visa (se terminant par 1234 - autorisation ABCD)'],
            ['fr-CA', 'card', 'amex', 'Amex (se terminant par 1234 - autorisation ABCD)'],
            ['fr-CA', 'card', 'mc', 'MasterCard (se terminant par 1234 - autorisation ABCD)'],
            ['fr-CA', 'card', 'discover', 'Discover (se terminant par 1234 - autorisation ABCD)'],
            ['fr-CA', 'vault', 'vault', 'Voûte sécurisée (se terminant par 1234 - autorisation ABCD)'],
            ['fr-CA', 'other', '', 'paiement alternatif (réf #DEFG)'],
            ['fr-CA', 'paypal', 'paypal', 'compte PayPal'],
            ['fr-CA', 'cash', 'cash', 'Argent comptant'],
            ['fr-CA', 'free', 'free', 'Gratuit'],

            ['es-MX', 'check', '', 'Cheque (#1 con fecha 14 oct., 2021)'],
            ['es-MX', 'ach', 'business account', 'Cuenta (terminando en 1234 - autorización ABCD)'],
            ['es-MX', 'ach', 'personal account', 'Cuenta (terminando en 1234 - autorización ABCD)'],
            ['es-MX', 'card', 'visa', 'Visa (terminando en 1234 - autorización ABCD)'],
            ['es-MX', 'card', 'vi', 'Visa (terminando en 1234 - autorización ABCD)'],
            ['es-MX', 'card', 'amex', 'Amex (terminando en 1234 - autorización ABCD)'],
            ['es-MX', 'card', 'mc', 'MasterCard (terminando en 1234 - autorización ABCD)'],
            ['es-MX', 'card', 'discover', 'Discover (terminando en 1234 - autorización ABCD)'],
            ['es-MX', 'vault', 'vault', 'Bóveda de seguridad (terminando en 1234 - autorización ABCD)'],
            ['es-MX', 'other', '', 'pago alternativo (ref #DEFG)'],
            ['es-MX', 'paypal', 'paypal', 'cuenta PayPal'],
            ['es-MX', 'cash', 'cash', 'Efectivo'],
            ['es-MX', 'free', 'free', 'Gratis'],
        ];
    }

    /** @dataProvider notifyParamsDataProvider */
    public function testNotifyParamsReturnsLocalizedParams(
        string $locale,
        string $card,
        string $paymentType,
        string $paymentTypeName,
        string $paymentTypeDescription
    ): void {
        sys_set('locale', $locale);

        $params = $this->orderWithRecurringProfile([
            'billingcardtype' => $card,
            'billingcardlastfour' => 1234,
            'confirmationnumber' => 5678,
            'check_number' => 101,
            'check_date' => '2020-10-15',
            'payment_other_reference' => 8910,
            'confirmationdatetime' => '2020-10-15',
            'payment_type' => $paymentType,
        ])->notifyParams();

        $this->assertStringContainsString($paymentTypeName, $params['bill_card_type']);
        $this->assertStringContainsString($paymentTypeName, $params['payment_type']);
        $this->assertStringContainsString($paymentTypeDescription, $params['payment_type_description']);
    }

    public function notifyParamsDataProvider(): array
    {
        return [
            ['en-CA', '', 'cash', 'Cash', 'Cash'],
            ['en-CA', '', 'check', 'Check', '(#101 dated Oct 15, 2020)'],
            ['en-CA', 'vault', 'vault', 'Secure Account', '(ending in 1234 - authorization 5678)'],
            ['en-CA', '', 'free', 'Free', 'Free'],
            ['en-CA', 'visa', 'vi', 'Visa', '(ending in 1234 - authorization 5678)'],
            ['en-CA', 'business check', 'ach', 'ACH', '(business account ending in 1234 - authorization 5678)'],
            ['en-CA', '', 'other', 'Other', 'alternate payment (ref #8910)'],
            ['en-CA', 'paypal', 'paypal', 'PayPal', 'PayPal account'],

            ['fr-CA', '', 'cash', 'Argent comptant', 'Argent comptant'],
            ['fr-CA', '', 'check', 'Cheque', '(#101 en date du 15 oct., 2020)'],
            ['fr-CA', 'vault', 'vault', 'Voûte sécurisée', '(se terminant par 1234 - autorisation 5678)'],
            ['fr-CA', '', 'free', 'Gratuit', 'Gratuit'],
            ['fr-CA', 'visa', 'vi', 'Visa', '(se terminant par 1234 - autorisation 5678)'],
            ['fr-CA', 'business check', 'ach', 'Compte', '(compte d\'entreprise se terminant par 1234 - autorisation 5678)'],
            ['fr-CA', '', 'other', 'Autre', 'paiement alternatif (réf #8910)'],
            ['fr-CA', 'paypal', 'paypal', 'PayPal', 'compte PayPal'],

            ['es-MX', '', 'cash', 'Efectivo', 'Efectivo'],
            ['es-MX', '', 'check', 'Cheque', '(#101 con fecha 15 oct., 2020)'],
            ['es-MX', 'vault', 'vault', 'Bóveda de seguridad', '(terminando en 1234 - autorización 5678)'],
            ['es-MX', '', 'free', 'Gratis', 'Gratis'],
            ['es-MX', 'visa', 'vi', 'Visa', '(terminando en 1234 - autorización 5678)'],
            ['es-MX', 'business check', 'ach', 'Cuenta', '(cuenta corporativa terminando en 1234 - autorización 5678)'],
            ['es-MX', '', 'other', 'Otro', 'pago alternativo (ref #8910)'],
            ['es-MX', 'paypal', 'paypal', 'PayPal', 'cuenta PayPal'],
        ];
    }

    /** @dataProvider notifiyParamStaticStringsDataProvider */
    public function testNotifyParamsCanLocalizeStaticStrings(
        string $locale,
        string $code,
        string $name,
        string $price,
        string $quantity,
        string $subtotal,
        string $shipping,
        string $tax,
        string $total,
        string $chargeDescription,
        string $starting
    ): void {
        sys_set('locale', $locale);

        $params = $this->orderWithRecurringProfile()->notifyParams();

        $this->assertStringContainsString($code, $params['invoice_table']);
        $this->assertStringContainsString($name, $params['invoice_table']);
        $this->assertStringContainsString($price, $params['invoice_table']);
        $this->assertStringContainsString($quantity, $params['invoice_table']);
        $this->assertStringContainsString($subtotal, $params['invoice_table']);
        $this->assertStringContainsString($shipping, $params['invoice_table']);
        $this->assertStringContainsString($tax, $params['invoice_table']);
        $this->assertStringContainsString($total, $params['invoice_table']);

        $this->assertStringContainsString($starting, $params['recurring_description']);
        $this->assertStringContainsString($chargeDescription, $params['recurring_description']);
    }

    public function notifiyParamStaticStringsDataProvider(): array
    {
        return [
            ['en-CA', 'Code', 'Name', 'Subtotal', 'Price', 'Qty', 'Shipping', 'Tax', 'Total', 'You will be charged', 'starting'],
            ['fr-CA', 'Code', 'Description', 'Prix', 'Qté', 'Sous-total', 'Livraison', 'Taxes', 'Total', 'Vous serez facturé', 'débutant le'],
            ['es-MX', 'Código', 'Articulo', 'Precio', 'Cantidad', 'Total parcial', 'Envío', 'Tasas', 'Total', 'Se le cobrará', 'a partir de'],
        ];
    }

    public function testTrashableMessagesToIncludeZeroDollarOrders()
    {
        $order = Order::factory()->create();
        $order->totalamount = 10;
        $order->save();

        $this->assertContains('This contribution was not data entered in POS.', $order->trashable_messages);
    }

    public function testTrashableMessagesNotToIncludeZeroDollarOrders()
    {
        $order = Order::factory()->pointOfSale()->create();

        $this->assertEmpty($order->trashable_messages);
    }

    public function testOrderIsAlwaysCompleteWhenNotFulfillable(): void
    {
        sys_set('use_givecloud_express', 1);
        $order = Order::factory()->create();

        $this->assertTrue($order->iscomplete);
    }

    /** @dataProvider isShippableDataProvider */
    public function testOrderReturnsValueWhenFulfillable(bool $isComplete = true, bool $assert = true): void
    {
        $order = Order::factory()->create(['iscomplete' => $isComplete]);
        $product = Product::factory()->create();
        $variant = Variant::factory()->for($product)->state(['isshippable' => true])->create();

        $order->addItem(['variant_id' => $variant->getKey(), 'qty' => 1]);
        $order->updateAggregates();

        $this->assertSame($assert, $order->iscomplete);
    }

    public function testIsFulfillableReturnsFalseWhenNotInGivecloudPro(): void
    {
        sys_set('use_givecloud_express', 1);
        $order = Order::factory()->create();

        $this->assertFalse($order->isFulfillable);
    }

    public function testIsFulfillableReturnsFalseWhenSettingIsNever(): void
    {
        sys_set('use_fulfillment', 'never');
        $order = Order::factory()->create();

        $this->assertFalse($order->isFulfillable);
    }

    public function testIsFulfillableReturnsTrueWhenSettingIsAlways(): void
    {
        sys_set('use_fulfillment', 'always');
        $order = Order::factory()->create();

        $this->assertTrue($order->isFulfillable);
    }

    /** @dataProvider isShippableDataProvider */
    public function testIsFulfillableReturnsHasShippableItems(bool $isShippable = true, bool $assert = true): void
    {
        $order = Order::factory()->create();
        $product = Product::factory()->create();
        $variant = Variant::factory()->for($product)->state(['isshippable' => $isShippable])->create();

        $order->addItem(['variant_id' => $variant->getKey(), 'qty' => 1]);
        $order->updateAggregates();

        $this->assertSame($assert, $order->isFulfillable);
    }

    public function isShippableDataProvider(): array
    {
        return [
            [true, true],
            [false, false],
        ];
    }

    private function orderWithRecurringProfile(array $state = []): Order
    {
        /** @var \Ds\Models\Order $order */
        $order = Order::factory()->paid()->shipped()->taxed()->dcc()->state($state)->create();

        $item = OrderItem::factory()->state([
            'price' => '19.99',
            'recurring_amount' => '19.99',
            'recurring_frequency' => RecurringFrequency::MONTHLY,
            'recurring_day' => 1,
            'recurring_day_of_week' => 2,
        ])->for($order)->create();

        RecurringPaymentProfile::factory()
            ->for(Product::factory())
            ->for($order)
            ->for(Account::factory(), 'member')
            ->for($item, 'order_item');

        $order->load('items');

        return $order;
    }
}
