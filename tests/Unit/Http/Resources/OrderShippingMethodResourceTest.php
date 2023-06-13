<?php

namespace Tests\Unit\Http\Resources;

use Ds\Http\Resources\OrderShippingMethodResource;
use Ds\Models\Order;
use Ds\Models\ShippingMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * @group resources
 * @group order
 */
class OrderShippingMethodResourceTest extends TestCase
{
    public function testToArray(): void
    {
        $order = Order::factory()->create();

        $orderResourceArray = $this->createResource($order);

        $this->assertArrayHasKey('title', $orderResourceArray);
        $this->assertEmpty($orderResourceArray['title']);
        $this->assertArrayHasKey('name', $orderResourceArray);
        $this->assertEmpty($orderResourceArray['name']);
        $this->assertArrayHasKey('courier', $orderResourceArray);
        $this->assertEmpty($orderResourceArray['courier']);
        $this->assertArrayHasKey('price', $orderResourceArray);
        $this->assertArrayHasKey('handle', $orderResourceArray);
        $this->assertArrayHasKey('value', $orderResourceArray);
    }

    public function testToArrayFreeShipping(): void
    {
        $order = Order::factory()->freeShipping()->create();

        $orderResourceArray = $this->createResource($order);

        $this->assertSame('FREE shipping', $orderResourceArray['title']);
        $this->assertEmpty($orderResourceArray['value']);
    }

    public function testToArrayWithShippingMethod(): void
    {
        $order = Order::factory()->create(['shipping_amount' => 1234]);
        $shippingMethod = ShippingMethod::factory()->create();
        $shippingMethod->orders()->save($order);

        $orderResourceArray = $this->createResource($order);

        $this->assertSame($order->shippingMethod->name, $orderResourceArray['title']);
        $this->assertStringStartsWith($order->shippingMethod->name, $orderResourceArray['name']);
        $this->assertEmpty($orderResourceArray['courier']);
        $this->assertSame($order->shipping_amount, $orderResourceArray['price']);
        $this->assertStringStartsWith(Str::slug($order->shippingMethod->name), $orderResourceArray['handle']);
        $this->assertSame($order->shipping_method_id, $orderResourceArray['value']);
    }

    public function testToArrayWithCourierMethod(): void
    {
        $order = Order::factory()->courier()->create(['shipping_amount' => 1234]);

        $orderResourceArray = $this->createResource($order);

        $this->assertSame($order->courier_method, $orderResourceArray['title']);
        $this->assertStringEndsWith($orderResourceArray['name'], $order->courier_method);
        $this->assertStringStartsWith($orderResourceArray['courier'], $order->courier_method);
        $this->assertSame($order->shipping_amount, $orderResourceArray['price']);
        $this->assertStringEndsWith($order->shipping_amount * 100, $orderResourceArray['handle']);
        $this->assertSame($order->courier_method, $orderResourceArray['value']);
    }

    private function createResource(Order $order): array
    {
        return (new OrderShippingMethodResource($order))->toArray(new Request());
    }
}
