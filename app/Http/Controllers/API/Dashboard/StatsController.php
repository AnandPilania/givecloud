<?php

namespace Ds\Http\Controllers\API\Dashboard;

use Ds\Http\Controllers\Controller;
use Ds\Services\StatsService;
use Illuminate\Http\JsonResponse;

class StatsController extends Controller
{
    public function __invoke(): JsonResponse
    {
        if (! user()->can('dashboard')) {
            return response()->json(['error' => 'You are not authorized to perform this action.'], 403);
        }

        $periods = app(StatsService::class)->periodsForDate(toUtc(fromLocal(request('month', 'now'))));

        $oneTimeRevenues = $this->stats($periods, 'oneTimeRevenues');
        $recurringRevenues = $this->stats($periods, 'recurringRevenues');
        $totalForPeriod = data_get($oneTimeRevenues, 'period.value') + data_get($recurringRevenues, 'period.value');
        $totalForPrevious = data_get($oneTimeRevenues, 'previous.value') + data_get($recurringRevenues, 'previous.value');

        return response()->json([
            'totals' => [
                'period' => money($totalForPeriod)->format('$0[.]0A'),
                'previous' => money($totalForPrevious)->format('$0[.]0A'),
                'diff' => app(StatsService::class)->difference($totalForPeriod, $totalForPrevious),
                'increasing' => $totalForPeriod - $totalForPrevious >= 0,
            ],
            'one_time' => $oneTimeRevenues,
            'recurring' => $recurringRevenues,
            'supporters' => $this->stats($periods, 'supporters', 'numeral'),
            'coverage' => $this->stats($periods, 'dccCoverage', 'numeral'),
            'contributions' => $this->stats($periods, 'contributions', 'numeral'),
            'dcc' => $this->stats($periods, 'dcc'),
            'order_amount' => $this->stats($periods, 'averageAmountPerOrder'),
            'daily_revenue' => $this->stats($periods, 'averageDailyRevenue'),
        ]);
    }

    public function stats(array $periods, string $method, ?string $format = 'money'): array
    {
        $period = app(StatsService::class)->forPeriod(...$periods['current'])->{$method}();
        $previous = app(StatsService::class)->forPeriod(...$periods['previous'])->{$method}();

        return [
            'diff' => app(StatsService::class)->difference($period, $previous),
            'increasing' => $period - $previous > 0,
            'period' => [
                'value' => $period,
                'formatted' => $format === 'money' ? money(round($period, 2))->format('$0[.]0A') : numeralFormat($period, '0[.]0A'),
            ],
            'previous' => [
                'value' => $previous,
                'formatted' => $format === 'money' ? money(round($previous, 2))->format('$0[.]0A') : numeralFormat($previous, '0[.]0A'),
            ],
        ];
    }
}
