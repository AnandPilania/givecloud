<?php

namespace Ds\Http\Controllers\Frontend\API\Account;

use Ds\Http\Controllers\Frontend\API\Controller;

class OrdersController extends Controller
{
    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOrders()
    {
        return $this->failure();
    }
}
