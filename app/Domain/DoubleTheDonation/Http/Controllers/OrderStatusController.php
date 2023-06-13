<?php

namespace Ds\Domain\DoubleTheDonation\Http\Controllers;

use Ds\Domain\DoubleTheDonation\DoubleTheDonationService;
use Ds\Domain\DoubleTheDonation\Http\Resources\RecordResource;
use Ds\Http\Controllers\API\Controller;
use Ds\Models\Order;

class OrderStatusController extends Controller
{
    public function __invoke(Order $order): RecordResource
    {
        return RecordResource::make(
            app(DoubleTheDonationService::class)->getDonationRecord($order->id)
        );
    }
}
