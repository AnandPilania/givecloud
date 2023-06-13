<?php

namespace Ds\Domain\Zapier\Controllers;

use Ds\Domain\Zapier\Resources\AccountResource;
use Ds\Domain\Zapier\Services\AccountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountsController
{
    /** @var \Ds\Domain\Zapier\Services\AccountService */
    protected $accountService;

    public function __construct(AccountService $accountService)
    {
        $this->accountService = $accountService;
    }

    public function index(Request $request): JsonResponse
    {
        $sampleAccount = $this->accountService->getRandomAccountOrFakeIt($request->user('passport'));

        return response()->json([new AccountResource($sampleAccount)]);
    }
}
