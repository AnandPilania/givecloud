<?php

namespace Ds\Services;

use Carbon\Carbon;
use Ds\Models\Member;
use Ds\Models\Order;
use Ds\Models\Transaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class StatsService
{
    public static Carbon $start;
    public static Carbon $end;

    public function difference($current, $initial): ?int
    {
        return rescueQuietly(fn () => round(($current - $initial) / $initial * 100));
    }

    public function forPeriod(Carbon $start, Carbon $end): StatsService
    {
        static::$start = $start;
        static::$end = $end;

        return $this;
    }

    public function period(): array
    {
        return [
            static::$start,
            static::$end,
        ];
    }

    public function periodsForDate(Carbon $date): array
    {
        $startCurrentPeriod = fromLocal($date)->startOfMonth()->toUtc();

        $endCurrentPeriod = $startCurrentPeriod->toLocal()->endOfMonth()->endOfDay()->toUtc();
        $endPreviousPeriod = $endCurrentPeriod->toLocal()->subMonthsNoOverflow()->endOfMonth()->toUtc();

        $startPreviousPeriod = $startCurrentPeriod->copy()->subMonthsNoOverflow();

        if ($startCurrentPeriod->month === fromLocal('now')->toUtc()->month
            && $startCurrentPeriod->year === fromLocal('now')->toUtc()->year) {
            $endCurrentPeriod = fromLocal('now')->toUtc();
            $endPreviousPeriod = $endCurrentPeriod->copy()->subMonthsNoOverflow();
        }

        return [
            'current' => [
                $startCurrentPeriod->toLocal()->startOfDay()->toUtc(),
                $endCurrentPeriod,
            ],
            'previous' => [
                $startPreviousPeriod->toLocal()->startOfDay()->toUtc(),
                $endPreviousPeriod,
            ],
        ];
    }

    public function periodize(Builder $query, string $column = 'ordered_at'): Builder
    {
        return $query->whereBetween($column, $this->period());
    }

    public function successfulContributions(): Builder
    {
        return $this->periodize(Order::paid())
            ->whereHas('payments', fn ($query) => $query->where('paid', true))
            ->notFullyRefunded();
    }

    public function successfulTransactions(): Builder
    {
        return $this->periodize(Transaction::succeeded(), 'order_time')
            ->whereHas('payments', fn ($query) => $query->where('paid', true)->where('refunded', false));
    }

    public function averageAmountPerOrder(): ?float
    {
        return $this->successfulContributions()->average(DB::raw('functional_total - (IFNULL(refunded_amt, 0) * functional_exchange_rate)'));
    }

    public function averageDailyRevenue(): ?float
    {
        $revenues = $this->oneTimeRevenues();
        $periods = $this->period();

        $days = $periods[0]->diffInDays($periods[1]);

        return rescueQuietly(fn () => $revenues / $days, 0);
    }

    public function contributions(): int
    {
        return $this->successfulContributions()->count();
    }

    public function dcc(): float
    {
        return $this->successfulContributions()->sum(DB::raw('dcc_total_amount * functional_exchange_rate'));
    }

    public function dccCoverage(): ?float
    {
        $coverage = $this->successfulContributions()->whereNull('refunded_at')->select(
            DB::raw('SUM(dcc_total_amount * functional_exchange_rate) /  SUM(functional_total  - (IFNULL(refunded_amt, 0) * functional_exchange_rate)) * 100 AS coverage')
        )->first();

        return data_get($coverage, 'coverage');
    }

    public function oneTimeRevenues(): float
    {
        return $this->successfulContributions()->sum(DB::raw('functional_total - (IFNULL(refunded_amt, 0) * functional_exchange_rate)'));
    }

    public function recurringRevenues(): float
    {
        return $this->successfulTransactions()->sum(DB::raw('functional_total - (IFNULL(refunded_amt, 0) * functional_exchange_rate)'));
    }

    public function supporters(): int
    {
        return $this->periodize(Member::query()->active(), 'first_payment_at')->count();
    }
}
