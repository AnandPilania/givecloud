<?php

namespace Ds\Domain\Analytics\Builders\FundraisingForms;

use Ds\Domain\Analytics\Builders\Builder;
use Ds\Domain\Analytics\Models\AnalyticsEvent;
use Ds\Models\Product;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;

class ViewsBuilder extends Builder
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
                DB::raw("SUM(IF(event_category = 'fundraising_forms.hosted_page', 1, 0)) as hosted_page_views"),
                DB::raw("SUM(IF(event_category = 'fundraising_forms.inline_embed', 1, 0)) as inline_embed_views"),
                DB::raw("SUM(IF(event_category = 'fundraising_forms.modal_embed', 1, 0)) as modal_embed_views"),
                DB::raw('COUNT(*) as total_views'),
            ])->fromSub(
                AnalyticsEvent::query()
                    ->addSelect([
                        'event_category',
                        'created_at',
                    ])->where('eventable_type', 'product')
                    ->where('eventable_id', $this->fundraisingForm->id)
                    ->opensImpressionsOrPageviews()
                    ->groupBy('analytics_visit_id', 'event_category'),
                'agg',
            );
    }
}
