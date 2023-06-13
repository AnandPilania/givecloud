<?php

namespace Ds\Domain\Zapier\Controllers;

use Ds\Domain\Zapier\Services\OrderService;
use Ds\Domain\Zapier\Services\TransactionService;
use Ds\Http\Resources\OrderResource;
use Ds\Http\Resources\TransactionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContributionController
{
    public function index(Request $request): JsonResponse
    {
        if ($request->get('recurring')) {
            $sampleTransaction = app(TransactionService::class)->getRandomTransactionOrFakeIt($request->user('passport'));

            return response()->json(TransactionResource::collection([$sampleTransaction]));
        }

        $sampleOrder = app(OrderService::class)->getRandomOrderOrFakeIt($request->user('passport'));

        return response()->json(OrderResource::collection([$sampleOrder]));
    }
}
