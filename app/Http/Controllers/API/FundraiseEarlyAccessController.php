<?php

namespace Ds\Http\Controllers\API;

use Illuminate\Http\JsonResponse;

class FundraiseEarlyAccessController extends Controller
{
    public function store(): JsonResponse
    {
        sys_set('fundraise_early_access_requested', '1');

        return response()->json([
            'requested' => sys_get('fundraise_early_access_requested'),
        ]);
    }
}
