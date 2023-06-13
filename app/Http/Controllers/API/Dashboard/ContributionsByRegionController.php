<?php

namespace Ds\Http\Controllers\API\Dashboard;

use Ds\Http\Controllers\Controller;
use Ds\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ContributionsByRegionController extends Controller
{
    public function __invoke(string $country_code): JsonResponse
    {
        if (! user()->can('dashboard')) {
            return response()->json(['error' => 'You are not authorized to perform this action.'], 403);
        }

        $regionData = [];
        Order::paid()
            ->select([
                DB::raw('billingstate as region'),
                DB::raw('count(*) AS value'),
            ])->groupBy('billingstate')
            ->where('billingcountry', $country_code)
            ->whereRaw("ifnull(billingstate,'') != ''")
            ->whereRaw('confirmationdatetime > DATE_SUB(NOW(), INTERVAL 1 YEAR)')
            ->getQuery()->get()->sortByDesc(function ($item) {
                return (int) $item->value;
            })->each(function ($item) use (&$regionData) {
                $regionData[] = [
                    'label' => $item->region,
                    'value' => $item->value,
                ];
            });

        return response()->json([
            'regionData' => $regionData,
        ]);
    }
}
