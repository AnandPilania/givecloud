<?php

namespace Ds\Domain\Analytics\Builders\FundraisingForms;

use Ds\Domain\Analytics\Builders\Builder;
use Ds\Domain\Analytics\Models\AnalyticsEvent;
use Ds\Models\Product;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;

class EngagedVisitsBuilder extends Builder
{
    /** @var \Ds\Models\Product */
    protected $fundraisingForm;

    /**
     * @return static
     */
    public function setFundraisingForm(Product $fundraisingForm): self
    {
        $this->fundraisingForm = $fundraisingForm;

        return $this;
    }

    public function getBuilder(): QueryBuilder
    {
        return $this->applyBuilder(DB::query())
            ->addSelect([
                DB::raw("SUM(IF(event_category = 'fundraising_forms.hosted_page', 1, 0)) as hosted_page_engaged_visits"),
                DB::raw("SUM(IF(event_category = 'fundraising_forms.inline_embed', 1, 0)) as inline_embed_engaged_visits"),
                DB::raw("SUM(IF(event_category = 'fundraising_forms.modal_embed', 1, 0)) as modal_embed_engaged_visits"),
                DB::raw('COUNT(*) as total_engaged_visits'),
            ])->fromSub(
                AnalyticsEvent::query()
                    ->addSelect([
                        'event_category',
                        'created_at',
                    ])->where('eventable_type', 'product')
                    ->where('eventable_id', $this->fundraisingForm->id)
                    ->whereIn('event_category', ['fundraising_forms.hosted_page', 'fundraising_forms.inline_embed', 'fundraising_forms.modal_embed'])
                    ->whereNotIn('event_name', ['impression', 'open', 'pageview'])
                    ->groupBy('analytics_visit_id', 'event_category'),
                'agg',
            );
    }
}
