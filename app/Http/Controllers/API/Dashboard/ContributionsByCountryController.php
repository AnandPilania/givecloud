<?php

namespace Ds\Http\Controllers\API\Dashboard;

use Ds\Common\ISO3166\ISO3166;
use Ds\Http\Controllers\Controller;
use Ds\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ContributionsByCountryController extends Controller
{
    public function __invoke(): JsonResponse
    {
        if (! user()->can('dashboard')) {
            return response()->json(['error' => 'You are not authorized to perform this action.'], 403);
        }

        $countryData = [];
        Order::paid()
            ->select([
                DB::raw('billingcountry as country_code'),
                DB::raw('count(*) AS value'),
            ])->groupBy('billingcountry')
            ->whereRaw("ifnull(billingcountry,'') != ''")
            ->whereRaw('confirmationdatetime > DATE_SUB(NOW(), INTERVAL 1 YEAR)')
            ->getQuery()->get()->sortByDesc(function ($item) {
                return (int) $item->value;
            })->each(function ($item) use (&$countryData) {
                $countryData[] = [
                    'code' => $item->country_code,
                    'label' => app(ISO3166::class)->country($item->country_code)['name'] ?? $item->country_code,
                    'value' => $item->value,
                ];
            });

        return response()->json([
            'countryData' => $countryData,
        ]);
    }
}
