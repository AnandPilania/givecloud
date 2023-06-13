<?php

namespace Ds\Repositories;

use Ds\Models\Order;
use Illuminate\Support\Facades\Cache;

class OrderRepository
{
    /** @var \Ds\Models\Order */
    protected $model;

    public function __construct(Order $model)
    {
        $this->model = $model;
    }

    public function getRandomPaidOrder(int $limitToLatest = 20): ?Order
    {
        $orders = $this->model->newQuery()
            ->paid()
            ->orderByDesc('id')
            ->take($limitToLatest)
            ->get();

        if ($orders->isEmpty()) {
            return null;
        }

        return $orders->random()->first();
    }

    /**
     * Retrieve incomplete Order count.
     *
     * @return int
     */
    public function getIncompleteCount(): int
    {
        if (Cache::has('incomplete_order_count')) {
            return Cache::get('incomplete_order_count');
        }

        $incompleteCount = Order::paid()->incomplete()->count();

        if ($incompleteCount > 10000) {
            $expires = now()->addMinutes(60);
        } elseif ($incompleteCount > 5000) {
            $expires = now()->addMinutes(30);
        } elseif ($incompleteCount > 2500) {
            $expires = now()->addMinutes(15);
        } else {
            $expires = now()->addMinutes(5);
        }

        Cache::put('incomplete_order_count', $incompleteCount, $expires);

        return $incompleteCount;
    }
}
