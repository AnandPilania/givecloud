<?php

namespace Ds\Http\Controllers\Reports;

use Ds\Domain\Shared\DataTable;
use Ds\Http\Controllers\Controller;
use Ds\Models\PledgeCampaign;
use Illuminate\Database\Eloquent\Builder as ElqouentBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use LiveControl\EloquentDataTable\ExpressionWithName;

class PledgeCampaignsController extends Controller
{
    /**
     * Register controller middleware.
     */
    protected function registerMiddleware()
    {
        $this->middleware('auth');
        $this->middleware('requires.feature:pledges');
    }

    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        user()->canOrRedirect('reports.pledge-campaigns');

        pageSetup('Pledge Campaigns', 'jpanel');

        $campaigns = $this->_basePledgeCampaignQuery()->get();

        return view('reports.pledge-campaigns', [
            '__menu' => 'reports.pledge-campaigns',
            'campaigns' => $campaigns,
        ]);
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function get()
    {
        user()->canOrRedirect('reports.pledge-campaigns');

        $query = $this->_baseQueryWithFilters();

        // generate data table
        $dataTable = new DataTable($query, [
            new ExpressionWithName('agg.id', 'id'),
            new ExpressionWithName('pledge_campaigns.name', 'campaign_name'),
            new ExpressionWithName('member.display_name', 'account_display_name'),
            new ExpressionWithName('agg.comments', 'comments'),
            new ExpressionWithName('agg.amount', 'amount'),
            new ExpressionWithName('agg.commitment_date', 'commitment_date'),

            // extras
            new ExpressionWithName('agg.type', 'type'),
            new ExpressionWithName('agg.account_id', 'account_id'),
            new ExpressionWithName('agg.order_number', 'order_number'),
            new ExpressionWithName('agg.pledge_campaign_id', 'pledge_campaign_id'),
            new ExpressionWithName('agg.is_anonymous', 'is_anonymous'),
            new ExpressionWithName('agg.recurring_interval', 'recurring_interval'),
            new ExpressionWithName('agg.currency_code', 'currency_code'),
            new ExpressionWithName('member.bill_email', 'account_bill_email'),
            new ExpressionWithName('member.bill_phone', 'account_bill_phone'),
        ]);

        // format results
        $dataTable->setFormatRowFunction(function ($pledgable) {
            return [
                $pledgable->type === 'order_item'
                    ? dangerouslyUseHTML('<a href="' . e(route('backend.orders.edit_without_id', ['c' => $pledgable->order_number])) . '" target="_blank"><i class="fa fa-search"></i></a>')
                    : dangerouslyUseHTML('<a href="/jpanel/pledges/' . e($pledgable->id) . '/modal" data-toggle="ajax-modal"><i class="fa fa-search"></i></a>'),
                dangerouslyUseHTML('<span class="text-muted"><strong>' . ($pledgable->type === 'order_item' ? 'DONATION' : 'PLEDGE') . '</strong></span>'),
                dangerouslyUseHTML('<strong><a href="' . e(route('backend.member.edit', $pledgable->account_id)) . '">' . e($pledgable->account_display_name) . '</a></strong><div class="text-muted">' . e($pledgable->account_bill_email) . '</div><div class="text-muted">' . e($pledgable->account_bill_phone) . '</div>'),
                dangerouslyUseHTML('<em>' . e(Str::limit($pledgable->comments, 200)) . '</em>'),
                dangerouslyUseHTML(e((string) money($pledgable->amount, $pledgable->currency_code)) . '&nbsp;<span class="text-muted">' . e($pledgable->currency_code) . '</span>' . e($pledgable->recurring_interval ? '/' . $this->getShortRecurringInterval($pledgable->recurring_interval) : '')),
                dangerouslyUseHTML(e(toLocalFormat($pledgable->commitment_date)) . ' <small class="text-muted">' . e(toLocalFormat($pledgable->commitment_date, 'g:iA')) . '</small>'),
            ];
        });

        // return datatable JSON
        return response($dataTable->make());
    }

    private function getShortRecurringInterval($interval)
    {
        if ($interval === 'weekly') {
            $interval = 'wk';
        } elseif ($interval === 'biweekly') {
            $interval = 'bi-wk';
        } elseif ($interval === 'monthly') {
            $interval = 'mth';
        } elseif ($interval === 'quarterly') {
            $interval = 'qr';
        } elseif ($interval === 'annually') {
            $interval = 'yr';
        }

        return $interval;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function export()
    {
        user()->canOrRedirect('reports.pledge-campaigns');

        $query = $this->_baseQueryWithFilters()->select([
            'agg.*',
            'pledge_campaigns.name as campaign_name',
            'member.display_name as account_display_name',
            'member.bill_email as account_bill_email',
            'member.bill_phone as account_bill_phone',
        ])->orderBy('commitment_date');

        return response()->streamDownload(function () use ($query) {
            $fp = fopen('php://output', 'w');

            fputcsv($fp, [
                'Campaign Name',
                'Type',
                'Name',
                'Email',
                'Phone',
                'Amount',
                'DCC Amount',
                'Total Amount',
                'Currency',
                'Recurring?',
                'Paid?',
                'Date',
                'Anonymous?',
                'Comments',
            ]);

            $query->chunk(250, function ($chunk) use ($fp) {
                foreach ($chunk as $pledgable) {
                    fputcsv($fp, [
                        $pledgable->campaign_name,
                        $pledgable->type === 'order_item' ? "CONTRIBUTION #{$pledgable->order_number}" : 'PLEDGE',
                        $pledgable->account_display_name,
                        $pledgable->account_bill_email,
                        $pledgable->account_bill_phone,
                        numeral($pledgable->amount),
                        numeral($pledgable->dcc_amount),
                        numeral($pledgable->amount + $pledgable->dcc_amount),
                        $pledgable->currency_code,
                        ucwords($pledgable->recurring_interval),
                        $pledgable->paid ? 'Y' : 'N',
                        toLocalFormat($pledgable->commitment_date, 'csv'),
                        $pledgable->is_anonymous ? 'Y' : 'N',
                        $pledgable->comments,
                    ]);
                }
            });

            fclose($fp);
        }, 'pledge-campaigns.csv', [
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Content-Description' => 'File Transfer',
            'Content-type' => 'text/csv',
            'Expires' => '0',
            'Pragma' => 'public',
        ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function _basePledgeCampaignQuery(): ElqouentBuilder
    {
        $query = PledgeCampaign::query();

        if (sys_get('limit_pledge_campaigns_report_to_created_campaigns') && ! user()->can('pledges')) {
            $query->where('created_by', user('id'));
        }

        return $query;
    }

    /**
     * @return \Illuminate\Database\Query\Builder
     */
    private function _baseQueryWithFilters(): Builder
    {
        /** @var \Ds\Models\PledgeCampaign */
        $campaign = $this->_basePledgeCampaignQuery()
            ->findOrFail(request('campaign'));

        $query = DB::query()
            ->fromSub(
                $campaign->orderItems()
                    ->select([
                        DB::raw("'order_item' as type"),
                        'productorderitem.id as id',
                        'productorder.invoicenumber as order_number',
                        'productorder.member_id as account_id',
                        DB::raw('IF(productorderitem.recurring_frequency IS NOT NULL, productorderitem.recurring_amount, productorderitem.price * productorderitem.qty) as amount'), // TEMPORARY FIX FOR TONY ROBBINS
                        'productorderitem.dcc_amount as dcc_amount',
                        'productorder.currency_code as currency_code',
                        'productorderitem.recurring_frequency as recurring_interval',
                        DB::raw('IF(productorderitem.price > 0, 1, 0) as paid'),
                        'productorder.confirmationdatetime as commitment_date',
                        'productorder.is_anonymous as is_anonymous',
                        'productorder.comments as comments',
                        '_pl.pledge_campaign_id as pledge_campaign_id',
                    ])->unionAll(
                        $campaign->pledges()->select([
                            DB::raw("'pledge' as type"),
                            'pledges.id as id',
                            DB::raw('NULL as order_number'),
                            'pledges.account_id as account_id',
                            'pledges.total_amount as amount',
                            DB::raw('0 as dcc_amount'),
                            'pledges.currency_code as currency_code',
                            DB::raw('NULL as recurring_interval'),
                            DB::raw('0 as paid'),
                            'pledges.created_at as commitment_date',
                            'pledges.is_anonymous as is_anonymous',
                            'pledges.comments as comments',
                            'pledges.pledge_campaign_id as pledge_campaign_id',
                        ])
                    ),
                'agg'
            )->join('member', 'member.id', 'agg.account_id')
            ->join('pledge_campaigns', 'pledge_campaigns.id', 'agg.pledge_campaign_id');

        $filters = (object) [];
        $filters->search = request('search');
        if ($filters->search) {
            $query->where(function ($query) use ($filters) {
                $query->orWhere('agg.comments', 'like', "%$filters->search%");
                $query->orWhere('member.display_name', 'like', "%$filters->search%");
                $query->orWhere('member.email', 'like', "%$filters->search%");
                $query->orWhere('member.bill_phone', 'like', "%$filters->search%");
            });
        }

        $filters->commitment_date_a = fromLocal(request('commitment_date_a'));
        if ($filters->commitment_date_a) {
            $query->where('agg.commitment_date', '>=', $filters->commitment_date_a->startOfDay());
        }

        $filters->commitment_date_b = fromLocal(request('commitment_date_b'));
        if ($filters->commitment_date_b) {
            $query->where('agg.commitment_date', '<=', toUtc($filters->commitment_date_b->endOfDay()));
        }

        return $query;
    }
}
