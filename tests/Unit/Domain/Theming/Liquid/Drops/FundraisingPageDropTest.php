<?php

namespace Tests\Unit\Domain\Theming\Liquid\Drops;

use Ds\Domain\Theming\Liquid\Drops\FundraisingPageDrop;
use Ds\Domain\Theming\Liquid\Drops\SocialProofDrop;
use Ds\Models\FundraisingPage;
use Ds\Models\Order;
use Ds\Models\OrderItem;
use Ds\Models\Product;
use Ds\Models\Variant;
use Tests\TestCase;

class FundraisingPageDropTest extends TestCase
{
    public function testSocialProof(): void
    {
        $order = $this->createOrderWithOrderItemsAndVariants();

        $fundraisingPageDrop = new FundraisingPageDrop($this->createFundraisingPage($order));
        $socialProofs = $fundraisingPageDrop->social_proof();

        $this->assertIsArray($socialProofs);
        $this->assertCount(3, $socialProofs);
        $this->assertContainsOnlyInstancesOf(SocialProofDrop::class, $socialProofs);
        foreach ($socialProofs as $socialProof) {
            $this->assertTrue($order->items->contains($socialProof->getSource()));
        }
    }

    public function testSocialProofRefundedOrderDoesNotShow(): void
    {
        $order = $this->createOrderWithOrderItemsAndVariants(['refunded_at' => now()]);

        $fundraisingPageDrop = new FundraisingPageDrop($this->createFundraisingPage($order));
        $socialProofs = $fundraisingPageDrop->social_proof();

        $this->assertIsArray($socialProofs);
        $this->assertEmpty($socialProofs);
    }

    protected function createFundraisingPage(Order $order): FundraisingPage
    {
        // Set same currency for system and fundraising page
        // to avoid calling Swap exchange rate facade.
        $currencyCode = 'CAD';
        sys_set('dpo_currency', $currencyCode);
        $fundraisingPage = FundraisingPage::factory()->create(['currency_code' => $currencyCode]);

        $fundraisingPage->paidOrderItems()->saveMany($order->items);

        return $fundraisingPage;
    }

    protected function createOrderWithOrderItemsAndVariants(array $orderAttributes = []): Order
    {
        // Order
        $order = Order::factory()->create(array_merge(['confirmationdatetime' => now()], $orderAttributes));

        // OrderItems
        $order->items()->saveMany(OrderItem::factory(3)->make());
        $order->load('items');

        // Variants
        $product = Product::factory()->create();
        $variant = Variant::factory()->create();
        $product->variants()->save($variant);
        $variant->orderItems()->saveMany($order->items);

        return $order;
    }
}
