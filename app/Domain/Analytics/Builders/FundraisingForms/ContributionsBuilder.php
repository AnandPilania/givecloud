<?php

namespace Ds\Domain\Analytics\Builders\FundraisingForms;

use Ds\Domain\Analytics\Builders\Builder;
use Ds\Models\Order;
use Ds\Models\Product;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

class ContributionsBuilder extends Builder
{
    /** @var \Ds\Models\Product */
    protected $fundraisingForm;

    /** @var int */
    protected $periodColumn = 'o.ordered_at';

    /** @var string|null */
    protected $sourceFilter = null;

    /**
     * @return static
     */
    public function setFundraisingForm(Product $fundraisingForm): self
    {
        $this->fundraisingForm = $fundraisingForm;

        return $this;
    }

    /**
     * @return static
     */
    public function setSourceFilter(?string $sourceFilter = null): self
    {
        $validSourceFilter = preg_match('/^(inline_embed|hosted_page|modal_embed)$/', (string) $sourceFilter);

        $this->sourceFilter = $validSourceFilter ? $sourceFilter : null;

        return $this;
    }

    protected function getBaseBuilder(): EloquentBuilder
    {
        $query = Order::from('productorder as o');
        $query->getModel()->setTable('o');

        return $this->applyBuilder($query)
            ->whereHas('items', fn ($query) => $query->whereIn('productinventoryid', $this->fundraisingForm->variants()->pluck('id')))
            ->whereNull('o.deleted_at')
            ->whereNotNull('o.confirmationdatetime')
            ->when($this->sourceFilter, function (EloquentBuilder $query) {
                $query->join('analytics_events as e1', function (JoinClause $join) {
                    $join->on('e1.event_value', 'o.id');
                    $join->where('e1.eventable_type', 'product');
                    $join->where('e1.eventable_id', $this->fundraisingForm->id);
                    $join->where('e1.event_name', 'contribution_paid');
                    $join->where('e1.event_category', 'fundraising_forms');
                });

                $query->whereExists(function (QueryBuilder $query) {
                    $query->from('analytics_events as e2')
                        ->select('e2.id')
                        ->where('e2.event_category', "fundraising_forms.{$this->sourceFilter}")
                        ->when($this->sourceFilter === 'inline_embed', fn (QueryBuilder $query) => $query->where('e2.event_name', 'impression'))
                        ->when($this->sourceFilter === 'hosted_page', fn (QueryBuilder $query) => $query->where('e2.event_name', 'pageview'))
                        ->when($this->sourceFilter === 'modal_embed', fn (QueryBuilder $query) => $query->where('e2.event_name', 'open'))
                        ->whereRaw('e2.analytics_visit_id = e1.analytics_visit_id')
                        ->limit(1);
                });
            });
    }

    public function getBuilder(): QueryBuilder
    {
        return $this->getBaseBuilder()
            ->addSelect([
                DB::raw('COUNT(o.id) as contribution_count'),
                DB::raw('ROUND(SUM(o.functional_total - (IFNULL(o.refunded_amt, 0) * o.functional_exchange_rate)), 2) as contribution_revenue'),
                DB::raw('ROUND(AVG(o.subtotal * o.functional_exchange_rate), 2) as contribution_average'),
                DB::raw('ROUND(MIN(o.subtotal * o.functional_exchange_rate), 2) as contribution_smallest'),
                DB::raw('ROUND(MAX(o.subtotal * o.functional_exchange_rate), 2) as contribution_largest'),
                DB::raw('SUM(IF(o.recurring_items = 0 OR (o.recurring_items = 1 AND i.id IS NOT NULL), 1, 0)) as onetime_contribution_count'),
                DB::raw('ROUND(SUM(IF(o.recurring_items = 0 OR (o.recurring_items = 1 AND i.id IS NOT NULL), o.functional_total - (IFNULL(o.refunded_amt, 0) * o.functional_exchange_rate), 0)), 2) as onetime_contribution_revenue'),
                DB::raw('SUM(IF(o.recurring_items = 1 AND i.id IS NULL, 1, 0)) as recurring_contribution_count'),
                DB::raw('ROUND(SUM(IF(o.recurring_items = 1 AND i.id IS NULL, o.functional_total - (IFNULL(o.refunded_amt, 0) * o.functional_exchange_rate), 0)), 2) as recurring_contribution_revenue'),
                DB::raw('ROUND(SUM(o.dcc_total_amount * o.functional_exchange_rate), 2) as dcc_revenue'),
                DB::raw('ROUND(AVG(o.dcc_total_amount * o.functional_exchange_rate), 2) as dcc_average'),
                DB::raw('ROUND(SUM(o.dcc_total_amount) / SUM(o.totalamount - IFNULL(o.refunded_amt, 0)) * o.functional_exchange_rate * 100, 1) as dcc_coverage'),
                DB::raw('SUM(IF(o.dcc_total_amount > 0, 1, 0)) as dcc_optin_count'),
                DB::raw('ROUND(IFNULL(SUM(IF(o.dcc_total_amount > 0, 1, 0)) / COUNT(o.id), 0) * 100, 1) as dcc_optin_conversion'),
                DB::raw('ROUND(SUM(IF(i.id IS NULL, 0, i.recurring_amount + i.dcc_recurring_amount)), 2) as upsell_revenue'),
                DB::raw('SUM(IF(i.id IS NULL, 0, 1)) as upsell_optin_count'),
                DB::raw('ROUND(IFNULL(SUM(IF(i.id IS NULL, 0, 1)) / SUM(IF(o.recurring_items = 0 OR i.id IS NOT NULL, 1, 0)), 0) * 100, 1) as upsell_optin_conversion'),
                DB::raw('SUM(IF(o.email_opt_in = 1, 1, 0)) as email_optin_count'),
                DB::raw('ROUND(IFNULL(SUM(IF(o.email_opt_in = 1, 1, 0)) / COUNT(o.id), 0) * 100, 1) as email_optin_conversion'),
                DB::raw("SUM(IF(o.doublethedonation_status = 'found', 1, 0)) as employer_matching_optin_count"),
                DB::raw("ROUND(IFNULL(SUM(IF(o.doublethedonation_status = 'found', 1, 0)) / SUM(IF(o.doublethedonation_status IS NOT NULL, 1, 0)), 0) * 100, 1) as employer_matching_optin_conversion"),
            ])->leftJoin('productorderitem as i', function (JoinClause $join) {
                $join->on('i.productorderid', 'o.id');
                $join->whereNotNull('i.locked_to_item_id');
            })->toBase();
    }
}
