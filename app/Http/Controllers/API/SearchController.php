<?php

namespace Ds\Http\Controllers\API;

use Ds\Services\SupporterSearchService;
use Illuminate\Http\JsonResponse;

class SearchController extends Controller
{
    public function __invoke(): JsonResponse
    {
        if (! $query = request('query')) {
            return response()->json(['error' => 'No query provided'], 400);
        }

        $results = app(SupporterSearchService::class)->handle($query);

        return response()->json($results);
    }
}
