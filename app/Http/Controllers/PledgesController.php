<?php

namespace Ds\Http\Controllers;

use Ds\Domain\Shared\DataTable;
use Ds\Events\PledgeCreated;
use Ds\Events\PledgeDeleted;
use Ds\Http\Requests\PledgesInsertFormRequest;
use Ds\Models\Pledge;
use Ds\Models\PledgeCampaign;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use LiveControl\EloquentDataTable\ExpressionWithName;

class PledgesController extends Controller
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
     * View pledges list.
     */
    public function index()
    {
        user()->canOrRedirect('pledges');

        $campaigns = PledgeCampaign::all();

        // return view
        return $this->getView('pledges/index', [
            '__menu' => 'products.pledges',
            'pageTitle' => 'Pledges',
            'campaigns' => $campaigns,
        ]);
    }

    /**
     * Ajax data for receipt list.
     */
    public function index_json()
    {
        user()->canOrRedirect('pledges');

        [$query] = $this->_baseQueryWithFilters();

        // generate data table
        $dataTable = new DataTable($query->with('account', 'campaign'), [
            new ExpressionWithName('pledges.id', 'id'),
            new ExpressionWithName('pledge_campaigns.name', 'campaign_name'),
            new ExpressionWithName('member.display_name', 'account_display_name'),
            new ExpressionWithName('pledges.funded_status', 'funded_status'),
            new ExpressionWithName('pledges.funded_count', 'funded_count'),
            new ExpressionWithName('pledges.funded_amount', 'funded_amount'),
            new ExpressionWithName('pledges.total_amount', 'total_amount'),
            new ExpressionWithName('pledges.created_at', 'created_at'),

            // extras
            'account_id',
            'pledge_campaign_id',
            new ExpressionWithName('member.bill_phone', 'account_bill_phone'),
            new ExpressionWithName('pledges.currency_code', 'currency_code'),
        ]);

        // format results
        $dataTable->setFormatRowFunction(function ($pledge) {
            return [
                dangerouslyUseHTML('<a href="/jpanel/pledges/' . e($pledge->id) . '/modal" data-toggle="ajax-modal"><i class="fa fa-search"></i></a>'),
                e($pledge->campaign_name),
                dangerouslyUseHTML('<strong><a href="' . route('backend.member.edit', $pledge->account_id) . '">' . e($pledge->account_display_name) . '</a></strong><div class="stat-lbl">' . e($pledge->account_bill_phone) . '</div>'),
                e($pledge->funded_status),
                e($pledge->funded_count),
                e((string) money($pledge->funded_amount, $pledge->currency_code)),
                dangerouslyUseHTML((string) money($pledge->total_amount, $pledge->currency_code) . '&nbsp;<span class="text-muted">' . e($pledge->currency_code) . '</span>'),
                dangerouslyUseHTML(e(toLocalFormat($pledge->created_at)) . ' <small class="text-muted">' . e(toLocalFormat($pledge->created_at, 'g:iA')) . '</small>'),
            ];
        });

        // return datatable JSON
        return response($dataTable->make());
    }

    /**
     * CSV output
     */
    public function index_csv()
    {
        user()->canOrRedirect('pledges');

        // output CSV
        header('Content-type: text/csv');
        header('Cache-Control: no-store, no-cache');
        header('Content-Disposition: attachment; filename="pledges.csv"');
        $outstream = fopen('php://output', 'w');

        [$pledges, $filters] = $this->_baseQueryWithFilters();

        $filters = array_filter(get_object_vars($filters));

        // aggregates
        if (empty($filters)) {
            fputcsv($outstream, ['SUMMARY'], ',', '"');
            fputcsv($outstream, [
                'Pledge',
                'Count',
                'Total Amount',
                'Funded Payments',
                'Funded Amount',
                'Funded Percent', ], ',', '"'); // new line
            $campaigns = PledgeCampaign::all();
            foreach ($campaigns as $campaign) {
                fputcsv($outstream, [
                    $campaign->name,
                    $campaign->total_count,
                    $campaign->total_amount,
                    $campaign->funded_count,
                    number_format($campaign->funded_amount, 2),
                    number_format($campaign->funded_percent, 2),
                ], ',', '"');
            }
        }

        // PLEDGES

        // generate data table
        $pledges = $pledges->select('pledges.*')
            ->with('account', 'campaign.products')
            ->get();

        if (empty($filters)) {
            fputcsv($outstream, [''], ',', '"'); // new line
            fputcsv($outstream, ['ALL PLEDGES'], ',', '"');
        }

        fputcsv($outstream, [
            'Pledge',
            'First Name',
            'Last Name',
            'Organization Name',
            'Email Address',
            'Phone',
            'Tracking Sales Of',
            'Starts',
            'Ends',
            'Total Amount',
            'Currency',
            'Funded Payments',
            'Funded Amount',
            'Funded Percent',
            'Funded Status',
            'Comments',
        ], ',', '"');
        foreach ($pledges as $pledge) {
            fputcsv($outstream, [
                $pledge->campaign->name,
                $pledge->account->first_name,
                $pledge->account->last_name,
                $pledge->account->bill_organization_name,
                $pledge->account->email,
                $pledge->account->bill_phone,
                $pledge->campaign->products->pluck('name')->implode(', '),
                fromLocalFormat($pledge->campaign->start_date, 'csv'),
                fromLocalFormat($pledge->campaign->end_date, 'csv'),
                number_format($pledge->total_amount, 2),
                $pledge->currency_code,
                $pledge->funded_count,
                $pledge->funded_amount,
                ($pledge->funded_percent * 100) . '%',
                $pledge->funded_status,
                $pledge->comments,
            ], ',', '"');
        }
        fclose($outstream);
        exit;
    }

    /**
     * View the modal
     */
    public function modal($id = 'new')
    {
        // tax receipts
        user()->canOrRedirect('pledges');

        if ($id == 'new') {
            $pledge = new Pledge;
            if (request('account_id')) {
                $pledge->account_id = request('account_id');
                $pledge->load('account');
            }
        } else {
            $pledge = Pledge::with(['account', 'campaign.products'])
                ->withTrashed()
                ->find($id);
        }

        // return view
        $this->setViewLayout(false);

        return $this->getView('pledges/modal', [
            'pledge' => $pledge,
            'pledgeCampaigns' => \Ds\Models\PledgeCampaign::all(),
            'payments' => $pledge->getPayments(),
        ]);
    }

    /**
     * Update the fundraiser
     */
    public function calculate($id)
    {
        // grab receipt
        $pledge = Pledge::find($id);

        if (! $pledge) {
            // $this->flash->success("Pledge does not exist or is archived.");
            return ['error' => 'Pledge does not exist or has been archived.'];
        }

        // force calculate
        $pledge->calculate(true);

        // $this->flash->success("Pledge recalculated successfully.");
        return ['success' => 'Pledge recalculated successfully.'];
    }

    /**
     * Create a new pledge
     */
    public function insert(PledgesInsertFormRequest $request)
    {
        $pledge = new Pledge;
        $this->_save($pledge, $request);

        event(new PledgeCreated($pledge));

        return [
            'success' => 'Pledge saved.',
            'pledge' => $pledge,
            'ajax-modal-redirect' => route('backend.pledges.modal', $pledge),
        ];
    }

    /**
     * Update the fundraiser
     */
    public function update(Request $request, $id)
    {
        $pledge = Pledge::find($id);
        $this->_save($pledge, $request);

        return ['success' => 'Pledge saved.', 'pledge' => $pledge];
    }

    /**
     * Save model from request input
     */
    private function _save(Pledge $pledge, Request $request): void
    {
        $pledge->account_id = $request->account_id;
        $pledge->pledge_campaign_id = $request->pledge_campaign_id;
        $pledge->currency_code = sys_get('dpo_currency');
        $pledge->total_amount = (float) preg_replace('/[^0-9.]/', '', $request->total_amount);
        $pledge->save();

        $pledge->calculate(true);
    }

    /**
     * Destroy the pledge
     */
    public function destroy($id)
    {
        // delete pledge
        $pledge = Pledge::find($id);
        $campaign = $pledge->campaign;
        $pledge->delete();

        $campaign->calculate(true);

        event(new PledgeDeleted($pledge));

        return ['success' => 'Pledge deleted.', 'pledge' => $pledge, 'ajax-modal-action' => 'close'];
    }

    private function _baseQueryWithFilters()
    {
        $query = Pledge::with('account', 'campaign.products')
            ->join('member', 'member.id', '=', 'pledges.account_id')
            ->join('pledge_campaigns', 'pledge_campaigns.id', '=', 'pledges.pledge_campaign_id');

        $filters = (object) [];
        $filters->search = request('search');
        if ($filters->search) {
            $query->where(function ($query) use ($filters) {
                $query->orWhere('pledge_campaigns.name', 'like', "%$filters->search%");
                $query->orWhere('pledges.comments', 'like', "%$filters->search%");
                $query->orWhere('member.display_name', 'like', "%$filters->search%");
                $query->orWhere('member.email', 'like', "%$filters->search%");
                $query->orWhere('member.bill_phone', 'like', "%$filters->search%");
            });
        }

        $filters->status = request('status');
        if ($filters->status) {
            $query->where('pledges.funded_status', '=', $filters->status);
        }

        $filters->product_ids = request('product_ids');
        if ($filters->product_ids) {
            $query->whereHas('campaign.products', function ($q) use ($filters) {
                $q->whereIn('id', $filters->product_ids);
            });
        }

        $filters->created_a = fromLocal(request('created_a'));
        if ($filters->created_a) {
            $query->where('pledges.created_at', '>=', $filters->created_a->startOfDay());
        }

        $filters->created_b = fromLocal(request('created_b'));
        if ($filters->created_b) {
            $query->where('pledges.created_at', '<=', toUtc($filters->created_b->endOfDay()));
        }

        $filters->campaigns = request('campaigns');
        if ($filters->campaigns) {
            $query->whereIn('pledges.pledge_campaign_id', Arr::wrap($filters->campaigns));
        }

        return [$query, $filters];
    }
}
