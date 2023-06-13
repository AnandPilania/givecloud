<?php

namespace Ds\Domain\Zapier\Services;

use Ds\Models\Order;
use Ds\Models\User;
use Ds\Repositories\OrderRepository;

class OrderService
{
    /** @var \Ds\Repositories\OrderRepository */
    protected $orderRepository;

    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function getRandomOrderOrFakeIt(User $apiUser): Order
    {
        return $this->orderRepository->getRandomPaidOrder() ?: $this->makeUser($apiUser);
    }

    protected function makeUser(User $apiUser): Order
    {
        /** @var \Ds\Models\Order */
        $fakeOrder = Order::factory()->make();

        $fakeOrder->createdBy = $apiUser;

        return $fakeOrder;
    }
}
