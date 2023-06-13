<?php

namespace Tests\Unit\Repositories;

use Ds\Models\Order;
use Ds\Repositories\OrderRepository;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OrderRepositoryTest extends TestCase
{
    public function testGetRandomPaidOrderWithLargeDataset(): void
    {
        $order = Order::factory()->paid()->make();
        DB::table($order->getTable())
            ->insert(
                array_map(function () use ($order) {
                    return [
                        'member_id' => $order->member_id,
                        'client_uuid' => $order->client_uuid,
                        'confirmationdatetime' => $order->confirmationdatetime,
                    ];
                }, range(0, 10000))
            );

        /** @var \Ds\Repositories\OrderRepository */
        $orderRepository = $this->app->make(OrderRepository::class);
        $startedAt = microtime(true);
        $randomOrder = $orderRepository->getRandomPaidOrder();
        $executionTime = microtime(true) - $startedAt;

        $this->assertInstanceOf(Order::class, $randomOrder);
        $this->assertLessThan(10, $executionTime * 1000);
    }
}
