<?php

namespace Ds\Http\Controllers\API\V2;

use Ds\Http\Queries\AccountsQuery;
use Ds\Http\Resources\AccountResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AccountController extends Controller
{
    public function index(AccountsQuery $query): AnonymousResourceCollection
    {
        user()->canOrRedirect(['member']);

        return AccountResource::collection($query->paginate());
    }

    public function show(AccountsQuery $query, string $accountId): AccountResource
    {
        user()->canOrRedirect(['member']);

        return new AccountResource($query->hashid($accountId)->first());
    }
}
