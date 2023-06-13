<?php

namespace Tests\Unit\Services\Orders;

use Ds\Enums\ProductType;
use Ds\Models\Order;
use Ds\Models\OrderItem;
use Ds\Models\Product;
use Ds\Models\Variant;
use Ds\Services\Order\OrderEmailPreferencesService;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OrderEmailPreferencesServiceTest extends TestCase
{
    use WithFaker;

    public function testHasValidEmailsValidatesEmail(): void
    {
        $order = Order::factory()->create([
            'billingemail' => $this->faker->email,
            'send_confirmation_emails' => true,
        ]);

        $this->assertTrue($this->app->make(OrderEmailPreferencesService::class)->shouldSendEmail($order));
    }

    /** @dataProvider invalidEmailDataProvider */
    public function testHasValidEmailsReturnsFalseOnInvalidOrEmptyEmails(?string $email): void
    {
        $order = Order::factory()->create([
            'billingemail' => $email,
            'send_confirmation_emails' => true,
        ]);

        $this->assertFalse($this->app->make(OrderEmailPreferencesService::class)->shouldSendEmail($order));
    }

    public function invalidEmailDataProvider(): array
    {
        return [
            ['invalid.email'],
            ['@gmail.com'],
            [''],
            [null],
        ];
    }

    public function testShouldSendEmailReflectsOrderColumn(): void
    {
        $order = Order::factory()->create([
            'billingemail' => $this->faker->email,
            'send_confirmation_emails' => 0,
        ]);

        $this->assertFalse($this->app->make(OrderEmailPreferencesService::class)->shouldSendEmail($order));

        $order = Order::factory()->create([
            'send_confirmation_emails' => 1,
            'billingemail' => $this->faker->email,
        ]);

        $this->assertTrue($this->app->make(OrderEmailPreferencesService::class)->shouldSendEmail($order));
    }

    public function testShouldSendLegacyEmailsReturnsFalseForDonationForms(): void
    {
        $order = $this->createOrderWithItem(['billingemail' => $this->faker->email], ['type' => ProductType::DONATION_FORM]);

        $this->assertFalse($this->app->make(OrderEmailPreferencesService::class)->shouldSendLegacyEmail($order));
        $this->assertTrue($this->app->make(OrderEmailPreferencesService::class)->shouldSendModernEmail($order));
    }

    public function testShouldSendModernEmailsReturnsFalseForNonDonationForms(): void
    {
        $order = $this->createOrderWithItem(['billingemail' => $this->faker->email], ['type' => ProductType::TEMPLATE]);

        $this->assertTrue($this->app->make(OrderEmailPreferencesService::class)->shouldSendLegacyEmail($order));
        $this->assertFalse($this->app->make(OrderEmailPreferencesService::class)->shouldSendModernEmail($order));
    }

    protected function createOrderWithItem(array $attributes = [], array $productAttributes = []): Order
    {
        return OrderItem::factory()
            ->for(Order::factory()->state($attributes))
            ->for(Variant::factory()->for(Product::factory()->state($productAttributes)))
            ->create()->order;
    }
}
