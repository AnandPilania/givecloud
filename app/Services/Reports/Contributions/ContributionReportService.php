<?php

namespace Ds\Services\Reports\Contributions;

use Ds\Models\Contribution;
use Illuminate\Database\Eloquent\Builder;

class ContributionReportService
{
    protected $filters = [
        Filters\CountryFilter::class,
        Filters\ContributionTypeFilter::class,
        Filters\DateRangeFilter::class,
        Filters\ItemTypeFilter::class,
        Filters\PaymentMethodFilter::class,
        Filters\RecurringFilter::class,
        Filters\ReferralFilter::class,
        Filters\RiskFilter::class,
        Filters\SearchFilter::class,
        Filters\StatusFilter::class,
        Filters\SourceFilter::class,
        Filters\SupporterTypeFilter::class,
        Filters\TrackingSourceFilter::class,
    ];

    public function filteredQuery(): Builder
    {
        $contributions = Contribution::query()
            ->leftJoin('member', 'supporter_id', 'member.id')
            ->with([
                'order' => fn ($query) => $query->withTrashed(),
                'order.items.lockedItems',
                'order.items.recurringPaymentProfile',
                'order.items.metadataRelation',
                'order.items.variant.metadataRelation',
                'order.items.variant.product',
                'order.payments',
                'payment',
                'supporter.latestAvatar',
                'transactions.recurringPaymentProfile',
            ]);

        return $this->applyFilters($contributions);
    }

    protected function applyFilters(Builder $query): Builder
    {
        collect($this->filters)->each(function ($filter) use ($query) {
            return (new $filter)($query);
        });

        return $query;
    }
}
