<?php

namespace Ds\Http\Controllers\API\Dashboard;

use Ds\Domain\QuickStart\QuickStartService;
use Ds\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ChecklistController extends Controller
{
    public function __invoke(): JsonResponse
    {
        if (! user()->can('dashboard')) {
            return response()->json(['error' => 'You are not authorized to perform this action.'], 403);
        }

        return response()->json([
            'status' => 'success',
            'tasks' => app(QuickStartService::class)->toArray(),
        ]);
    }
}
