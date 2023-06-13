<?php

namespace Ds\Http\Controllers\API\Dashboard;

use Ds\Http\Controllers\Controller;
use Ds\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ChartsController extends Controller
{
    public function __invoke(): JsonResponse
    {
        if (! user()->can('dashboard')) {
            return response()->json(['error' => 'You are not authorized to perform this action.'], 403);
        }

        // ////////////////////////////////
        // 30 day revenues
        try {
            $sales_days = 60;
            $start_date = fromLocal("Today -{$sales_days} days");
            $end_date = fromLocal('today');

            $one_time = DB::table('productorder')
                ->select(DB::raw("date(convert_tz(confirmationdatetime, 'UTC', '" . localOffset() . "')) AS `order_date`, SUM(functional_total) AS amount"))
                ->where('confirmationdatetime', '>', now()->subDays($sales_days))
                ->where('is_processed', 1)
                ->whereNull('deleted_at')
                ->groupBy('order_date')
                ->orderBy('confirmationdatetime')
                ->pluck('amount', 'order_date');

            $recurring = DB::table('transactions')
                ->select(DB::raw("date(convert_tz(order_time, 'UTC', '" . localOffset() . "')) AS `order_date`, SUM(functional_total) AS amount"))
                ->where('order_time', '>', now()->subDays($sales_days))
                ->where('payment_status', 'Completed')
                ->groupBy('order_date')
                ->orderBy('order_time')
                ->pluck('amount', 'order_date');

            $curr_date = $start_date->copy();
            $revenue_chart = [];

            while ($curr_date->lte($end_date)) {
                $key = $curr_date->format('Y-m-d');
                $revenue_chart[$key] = (object) ['order_date' => $key];
                $revenue_chart[$key]->one_time = (float) ($one_time[$key] ?? 0);
                $revenue_chart[$key]->recurring = (float) ($recurring[$key] ?? 0);
                $curr_date->addDay();
            }

            $revenue_chart = array_values($revenue_chart);
        } catch (\Exception $e) {
            $revenue_chart = [
                'message' => 'Error Loading Info...',
            ];
        }

        // ////////////////////////////////
        // best sellers
        try {
            $best_seller_chart_data = DB::table('product as p')
                ->select('p.name', DB::raw('count(*) AS sales_count'))
                ->join('productinventory AS iv', 'iv.productid', '=', 'p.id')
                ->join('productorderitem AS i', 'i.productinventoryid', '=', 'iv.id')
                ->join('productorder AS o', 'o.id', '=', 'i.productorderid')
                ->groupBy('p.name')
                ->orderBy('sales_count', 'desc')
                ->whereNotNull('p.name')
                ->whereNotNull('o.ordered_at')
                ->whereNull('o.deleted_at')
                ->whereRaw('o.confirmationdatetime > DATE_SUB(NOW(), INTERVAL 1 YEAR)')
                ->limit(5)
                ->get();
        } catch (\Exception $e) {
            $best_seller_chart_data = [
                'message' => 'Error Loading Info...',
            ];
        }

        // ////////////////////////////////
        // today's engagement
        try {
            $engagement_chart_data = [
                [
                    'label' => 'Empty Carts',
                    'color' => '#FC58AF',
                    'value' => (int) DB::table('productorder AS o')
                        ->selectRaw('COUNT(DISTINCT o.id) AS counter')
                        ->leftJoin('productorderitem AS i', 'i.productorderid', '=', 'o.id')
                        ->whereRaw('(i.id IS NULL OR i.qty = 0)')
                        ->whereNull('o.confirmationdatetime')
                        ->whereNull('o.deleted_at')
                        ->whereBetween('o.started_at', [
                            fromLocal('now')->startOfDay()->toUtc(),
                            fromLocal('now')->endOfDay()->toUtc(),
                        ])->value('counter'),
                ],
                [
                    'label' => 'Carts w/ Items',
                    'color' => '#0066FF',
                    'value' => (int) DB::table('productorder AS o')
                        ->selectRaw('COUNT(DISTINCT o.id) AS counter')
                        ->join('productorderitem AS i', 'i.productorderid', '=', 'o.id')
                        ->where('i.qty', '>', 0)
                        ->whereNull('o.billingemail')
                        ->whereNull('o.confirmationdatetime')
                        ->whereNull('o.deleted_at')
                        ->whereBetween('o.started_at', [
                            fromLocal('now')->startOfDay()->toUtc(),
                            fromLocal('now')->endOfDay()->toUtc(),
                        ])->value('counter'),
                ],
                [
                    'label' => 'In Checkout',
                    'color' => '#42CFFC',
                    'value' => (int) DB::table('productorder AS o')
                        ->selectRaw('COUNT(DISTINCT o.id) AS counter')
                        ->join('productorderitem AS i', 'i.productorderid', '=', 'o.id')
                        ->where('i.qty', '>', 0)
                        ->whereNotNull('o.billingemail')
                        ->whereNull('o.confirmationdatetime')
                        ->whereNull('o.deleted_at')
                        ->whereBetween('o.started_at', [
                            fromLocal('now')->startOfDay()->toUtc(),
                            fromLocal('now')->endOfDay()->toUtc(),
                        ])->value('counter'),
                ],
                [
                    'label' => 'Completed Contributions',
                    'color' => '#6C87F0',
                    'value' => (int) Order::paid()
                        ->whereBetween('started_at', [
                            fromLocal('now')->startOfDay()->toUtc(),
                            fromLocal('now')->endOfDay()->toUtc(),
                        ])->count(),
                ],
            ];

            if ($engagement_chart_data[0]['value'] == 0 && $engagement_chart_data[1]['value'] == 0 && $engagement_chart_data[2]['value'] == 0 && $engagement_chart_data[3]['value'] == 0) {
                $engagement_chart_data = [
                    'message' => 'Nothing here... yet.',
                ];
            }
        } catch (\Exception $e) {
            $engagement_chart_data = [
                'message' => 'Error Loading Info...',
            ];
        }

        // ////////////////////////////////
        // 30 day account growth
        try {
            $size_now = \Ds\Models\Member::active()->count();
            if ($size_now == 0) {
                $account_growth_chart_data_30day = [
                    'message' => 'Nothing here... yet.',
                ];
            } else {
                $initial_size = (int) DB::table('member')
                    ->selectRaw('COUNT(*) AS initial_count')
                    ->whereRaw('created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)')
                    ->where('is_active', true)
                    ->value('initial_count');

                $qAccountGrowth = db_query('SELECT DATE(created_at) AS `created_at`, COUNT(*) AS growth
                    FROM `member`
                    WHERE is_active = 1
                    GROUP BY DATE(created_at)
                    ORDER BY created_at ASC');

                $start_date = fromLocal('Today -30 days');
                $end_date = fromLocal('today');
                $account_growth_chart_data_30day = query_to_dated_array($qAccountGrowth, $start_date, $end_date, 'created_at', 'growth');
                $newSize = $initial_size;
                foreach ($account_growth_chart_data_30day as $data) {
                    $newSize += $data->growth;
                    $data->growth = $newSize;
                }
            }
        } catch (\Exception $e) {
            $account_growth_chart_data_30day = [
                'message' => 'Error Loading Info...',
            ];
        }

        return response()->json(
            compact(
                'revenue_chart',
                'best_seller_chart_data',
                'engagement_chart_data',
                'account_growth_chart_data_30day',
            )
        );
    }
}
