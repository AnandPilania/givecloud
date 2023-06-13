<?php

namespace Ds\Domain\Analytics\Builders\FundraisingForms;

use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;

class ContributionsMedianBuilder extends ContributionsBuilder
{
    /** @var bool */
    protected $applyGroupBy = false;

    public function getBuilder(): QueryBuilder
    {
        $contributionAmounts = $this->getBaseBuilder()
            ->addSelect(DB::raw('(o.subtotal * o.functional_exchange_rate) as amount'))
            ->orderBy('period')
            ->orderBy('amount');

        $contributions = DB::query()
            ->select([
                'period',
                'amount',
                DB::raw('@rownum := @rownum + 1 as contribution_sequence'),
            ])->fromSub($contributionAmounts, 'contribution_amounts')
            ->crossJoinSub('SELECT @rownum := 0', 'r')
            ->orderBy('period')
            ->orderBy('contribution_sequence');

        $agg = DB::query()
            ->select([
                'period',
                'amount',
                'contribution_sequence',
                'ct' => $this->getBaseBuilder()->select(DB::raw('COUNT(*)'))->whereRaw('DATE(o.ordered_at) = contributions.period'),
                'delta' => $this->getBaseBuilder()->select(DB::raw('COUNT(*)'))->whereRaw('DATE(o.ordered_at) < contributions.period'),
            ])->fromSub($contributions, 'contributions')
            ->havingRaw('(ct % 2 = 0 AND contribution_sequence - delta BETWEEN FLOOR((ct + 1) / 2) and FLOOR((ct + 1) / 2) + 1) OR (ct % 2 <> 0 AND contribution_sequence - delta = (ct + 1) / 2)');

        return DB::query()
            ->select([
                'period',
                DB::raw('ROUND(AVG(amount), 2) as contribution_median'),
            ])->fromSub($agg, 'agg')
            ->groupBy('period');
    }
}
