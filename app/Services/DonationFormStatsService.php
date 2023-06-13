<?php

namespace Ds\Services;

use Ds\Domain\Analytics\Models\AnalyticsEvent;
use Ds\Models\Order;
use Ds\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DonationFormStatsService
{
    public function all(Product $product): array
    {
        return [
            'revenues' => $this->revenues($product),
            'views' => $this->views($product),
            'donors' => $this->donors($product),
            'conversions' => $this->conversions($product),
        ];
    }

    public function revenues(Product $product): array
    {
        $revenues = Order::query()
            ->select([
                DB::raw('DATE(ordered_at) as date'),
                DB::raw('SUM(functional_total - (IFNULL(refunded_amt, 0) * functional_exchange_rate)) as revenue'),
            ])->whereHas('items', fn ($query) => $query->whereIn('productinventoryid', $product->variants()->pluck('id')))
            ->where('ordered_at', '>', toUtc('now')->subDays(60))
            ->whereNull('productorder.deleted_at')
            ->whereNotNull('productorder.confirmationdatetime')
            ->groupBy(DB::raw('DATE(ordered_at)'))
            ->pluck('revenue', 'date')
            ->map(fn ($revenue) => (float) $revenue);

        return $this->trendify($product, $revenues);
    }

    public function views(Product $product)
    {
        $views = AnalyticsEvent::query()
            ->select([
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(id) as views'),
            ])->where(function (Builder $query) {
                $query->where(function (Builder $query) {
                    $query->where('event_category', 'fundraising_forms.modal_embed')->where('event_name', 'open');
                })->orWhere(function (Builder $query) {
                    $query->where('event_category', 'fundraising_forms.hosted_page')->where('event_name', 'pageview');
                })->orWhere(function (Builder $query) {
                    $query->where('event_category', 'fundraising_forms.inline_embed')->where('event_name', 'impression');
                });
            })->where('eventable_type', 'product')
            ->where('eventable_id', $product->id)
            ->where('created_at', '>', toUtc('now')->subDays(60))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->pluck('views', 'date');

        return $this->trendify($product, $views);
    }

    public function donors(Product $product)
    {
        $lastPeriod = Order::query()
            ->paid()
            ->notFullyRefunded()
            ->join('productorderitem', 'productorderitem.productorderid', '=', 'productorder.id')
            ->join('productinventory', 'productinventory.id', '=', 'productorderitem.productinventoryid')
            ->select('member_id')
            ->distinct()
            ->where('ordered_at', '>', toUtc('now')->subDays(30))
            ->where('productinventory.productid', $product->id)
            ->count();

        $previousPeriod = Order::query()
            ->paid()
            ->notFullyRefunded()
            ->join('productorderitem', 'productorderitem.productorderid', '=', 'productorder.id')
            ->join('productinventory', 'productinventory.id', '=', 'productorderitem.productinventoryid')
            ->select('member_id')
            ->distinct()
            ->whereBetween('ordered_at', [toUtc('now')->subDays(60), toUtc('now')->subDays(30)])
            ->where('productinventory.productid', $product->id)
            ->count();

        return [
            'trend' => ! $this->hasEnoughDataToTrend($product) || $lastPeriod <= 0 || $previousPeriod <= 0
                ? -1
                : app(StatsService::class)->difference($lastPeriod, $previousPeriod),
            'previousPeriod' => $previousPeriod,
            'lastPeriod' => $lastPeriod,
        ];
    }

    public function conversions(Product $product)
    {
        $visits = AnalyticsEvent::query()
            ->join('analytics_visits', 'analytics_visits.id', 'analytics_events.analytics_visit_id')
            ->select('visitor_id')
            ->where(function (Builder $query) {
                $query->where(fn (Builder $query) => $query->where('event_category', 'fundraising_forms.modal_embed')->where('event_name', 'open'))
                    ->orWhere(fn (Builder $query) => $query->where('event_category', 'fundraising_forms.hosted_page')->where('event_name', 'pageview'))
                    ->orWhere(fn (Builder $query) => $query->where('event_category', 'fundraising_forms.inline_embed')->where('event_name', 'impression'));
            })->where('eventable_type', 'product')
            ->where('eventable_id', $product->id)
            ->distinct();

        $contributions = AnalyticsEvent::query()
            ->where('event_name', 'contribution_paid')
            ->where('event_category', 'fundraising_forms')
            ->where('eventable_type', 'product')
            ->where('eventable_id', $product->id);

        $lastPeriodVisits = $visits->where('analytics_events.created_at', '>', toUtc('now')->subDays(30))->count('visitor_id');
        $lastPeriodContributions = $contributions->where('analytics_events.created_at', '>', toUtc('now')->subDays(30))->count();

        $previousPeriodVisits = $visits->whereBetween('analytics_events.created_at', [toUtc('now')->subDays(60), toUtc('now')->subDays(30)])->count('visitor_id');
        $previousPeriodContributions = $contributions->whereBetween('analytics_events.created_at', [toUtc('now')->subDays(60), toUtc('now')->subDays(30)])->count();

        $lastPeriodConversion = rescueQuietly(fn () => $lastPeriodContributions / $lastPeriodVisits * 100);
        $previousPeriodConversion = rescueQuietly(fn () => $previousPeriodContributions / $previousPeriodVisits * 100);

        return [
            'trend' => $this->hasEnoughDataToTrend($product) && $previousPeriodVisits && $lastPeriodVisits
                ? app(StatsService::class)->difference($lastPeriodConversion, $previousPeriodConversion)
                : -1,
            'previousPeriod' => is_nan($previousPeriodConversion) ? 0 : (int) $previousPeriodConversion,
            'lastPeriod' => is_nan($lastPeriodConversion) ? 0 : (int) $lastPeriodConversion,
        ];
    }

    protected function trendify(Product $product, Collection $data)
    {
        $dated = $this->dateRange($data);

        $previousPeriod = $dated->slice(0, 29)->sum();
        $lastPeriod = $dated->slice(-30)->sum();

        $trend = app(StatsService::class)->difference($lastPeriod, $previousPeriod);

        return [
            'data' => $dated,
            'trend' => $this->hasEnoughDataToTrend($product) && $previousPeriod && $lastPeriod ? $trend : -1,
            'previousPeriod' => $previousPeriod,
            'lastPeriod' => $lastPeriod,
        ];
    }

    protected function dateRange(Collection $data): Collection
    {
        $end_date = toLocal('today');
        $start_date = toLocal('today')->subDays(59);

        $records = [];

        while ($start_date->lte($end_date)) {
            $key = $start_date->format('Y-m-d');
            $records[$key] = $data->get($key) ?: 0;

            $start_date->addDay();
        }

        return collect($records);
    }

    protected function hasEnoughDataToTrend(Product $product): bool
    {
        return
            Order::query()
                ->paid()
                ->notFullyRefunded()
                ->join('productorderitem', 'productorderitem.productorderid', '=', 'productorder.id')
                ->join('productinventory', 'productinventory.id', '=', 'productorderitem.productinventoryid')
                ->where('productinventory.productid', $product->id)
                ->where('ordered_at', '<=', toUtc('now')->subDays(59))
                ->exists();
    }
}
