<?php

namespace Ds\Repositories;

use Ds\Models\PledgeCampaign;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class PledgableTotalAmountRepository
{
    public function get(PledgeCampaign $campaign): float
    {
        // contributions are automatically applied to pledges so we need to ensure that pledge
        // amounts are not double counted so we take that larger of the two
        return (float) DB::query()
            ->selectRaw('sum(greatest(pledge_amounts, contribution_amounts)) as pledgable_total_amount')
            ->fromSub(
                DB::query()
                    ->select([
                        DB::raw("sum(if(object_type = 'pledge', amount, 0)) as pledge_amounts"),
                        DB::raw("sum(if(object_type = 'order_item', amount, 0)) as contribution_amounts"),
                    ])->fromSub($this->getBuilderForSupporterAmounts($campaign), '_supporter_amounts')
                    ->groupBy('supporter'),
                'agg'
            )->value('pledgable_total_amount');
    }

    private function getBuilderForSupporterAmounts(PledgeCampaign $campaign): Builder
    {
        return $this->getBuilderForSupporterPledgeAmounts($campaign)
            ->unionAll($this->getBuilderForSupporterContributionAmounts($campaign));
    }

    private function getBuilderForSupporterPledgeAmounts(PledgeCampaign $campaign): Builder
    {
        return $campaign->pledges()
            ->select([
                DB::raw("'pledge' as object_type"),
                'pledges.account_id as supporter',
                'pledges.functional_total_amount as amount',
            ])->toBase();
    }

    private function getBuilderForSupporterContributionAmounts(PledgeCampaign $campaign): Builder
    {
        return $campaign->orderItems()
            ->select([
                DB::raw("'order_item' as object_type"),
                'productorder.member_id as supporter',
                DB::raw(collect([
                    '(',
                    'if(',
                    'productorderitem.recurring_frequency is not null, ',
                    'productorderitem.recurring_amount, ',
                    'productorderitem.price * productorderitem.qty',
                    ') * productorder.functional_exchange_rate',
                    ') as amount',
                ])->implode('')),
            ])->whereNull('productorder.refunded_at')
            ->toBase();
    }
}
