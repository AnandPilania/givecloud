<?php

namespace Ds\Http\Controllers\API\V2;

use Ds\Http\Queries\OrdersQuery;
use Ds\Http\Queries\TransactionsQuery;
use Ds\Http\Resources\OrderResource;
use Ds\Http\Resources\TransactionResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class ContributionController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        if ($request->get('recurring')) {
            user()->canOrRedirect(['recurringpaymentprofile']);

            return TransactionResource::collection(app(TransactionsQuery::class)->succeeded()->paginate()->withQueryString());
        }

        user()->canOrRedirect(['order']);

        return OrderResource::collection(app(OrdersQuery::class)->paid()->paginate()->withQueryString());
    }

    public function show(string $hashId): JsonResource
    {
        if (Str::startsWith($hashId, 'txn_')) {
            user()->canOrRedirect(['recurringpaymentprofile']);

            return new TransactionResource(app(TransactionsQuery::class)->hashid($hashId)->succeeded()->firstOrFail());
        }

        user()->canOrRedirect(['order']);

        return new OrderResource(app(OrdersQuery::class)->hashid($hashId)->paid()->firstOrFail());
    }
}
