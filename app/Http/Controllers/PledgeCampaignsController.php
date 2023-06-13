<?php

namespace Ds\Http\Controllers;

use Ds\Domain\Shared\DataTable;
use Ds\Models\PledgeCampaign;

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
     * View tax receipt list.
     */
    public function index()
    {
        user()->canOrRedirect('pledgecampaigns');

        // return view
        return $this->getView('pledges/campaigns/index', [
            '__menu' => 'products.pledges',
            'pageTitle' => 'Pledge Campaigns',
            'stats' => [],
        ]);
    }

    /**
     * Ajax data for receipt list.
     */
    public function index_json()
    {
        user()->canOrRedirect('pledgecampaigns');

        // generate data table
        $dataTable = new DataTable($this->_baseQueryWithFilters()->with('products'), [
            'id',
            'name',
            'start_date',
            'end_date',
            'total_count',
            'total_amount',
            'funded_amount',
            'funded_percent',
        ]);

        // format results
        $dataTable->setFormatRowFunction(function ($type) {
            return [
                dangerouslyUseHTML('<a href="/jpanel/pledges/campaigns/' . e($type->id) . '/modal" data-toggle="ajax-modal"><i class="fa fa-search"></i></a>'),
                e($type->name),
                e(fromLocalFormat($type->start_date)),
                e(fromLocalFormat($type->end_date)),
                e($type->products->pluck('name')->implode(', ')),
                e(number_format($type->total_count, 0)),
                e(number_format($type->total_amount, 2)),
                e(number_format($type->funded_amount, 2)),
                dangerouslyUseHTML('<div class="progress progress-sm" style="width:120px; margin-bottom:0px;">') .
                    dangerouslyUseHTML('<div class="progress-bar progress-bar-info" style="width:' . e($type->funded_percent * 100) . '%;"></div>') .
                dangerouslyUseHTML('</div>'),
            ];
        });

        // return datatable JSON
        return response($dataTable->make());
    }

    /**
     * View the modal
     */
    public function modal($id = 'new')
    {
        user()->canOrRedirect('pledgecampaigns');

        if ($id == 'new') {
            $pledgeCampaign = new PledgeCampaign;
        } else {
            $pledgeCampaign = PledgeCampaign::withTrashed()
                ->find($id);
        }

        // return view
        $this->setViewLayout(false);

        return $this->getView('pledges/campaigns/modal', [
            'pledgeCampaign' => $pledgeCampaign,
        ]);
    }

    /**
     * Create a new pledge
     */
    public function insert()
    {
        user()->canOrRedirect('pledgecampaigns');

        // save pledge
        $pledgeCampaign = new PledgeCampaign;
        $this->_save($pledgeCampaign);

        return ['success' => 'Pledge type saved.', 'pledgeCampaign' => $pledgeCampaign, 'ajax-modal-action' => 'refresh'];
        // $this->flash->success("'" . $fundraiser->title . "' page has been saved.");
        // return redirect()->to('/jpanel/fundraising-pages/'.$fundraiser->id);
    }

    /**
     * Update the fundraiser
     */
    public function update($id)
    {
        user()->canOrRedirect('pledgecampaigns');

        // save pledge
        $pledgeCampaign = PledgeCampaign::find($id);
        $this->_save($pledgeCampaign);

        $pledgeCampaign->calculatePledges();
        $pledgeCampaign->calculate(true);

        return ['success' => 'Pledge saved.', 'pledgeCampaign' => $pledgeCampaign];
    }

    private function _save($pledgeCampaign)
    {
        $pledgeCampaign->name = request('name');
        $pledgeCampaign->start_date = request('start_date');
        $pledgeCampaign->end_date = request('end_date');
        $pledgeCampaign->save();

        // attach products
        $pledgeCampaign->products()->sync(request('product_ids'));
    }

    /**
     * Destroy the pledge
     */
    public function destroy($id)
    {
        user()->canOrRedirect('pledgecampaigns');

        // delete pledge
        $campaign = PledgeCampaign::find($id);
        $campaign->delete();

        return ['success' => 'Pledge campaign deleted.', 'campaign' => $campaign, 'ajax-modal-action' => 'close'];
    }

    private function _baseQueryWithFilters()
    {
        $query = PledgeCampaign::query();

        $filters = (object) [];
        $filters->search = request('search');
        if ($filters->search) {
            $query->where(function ($query) use ($filters) {
                $query->where('pledge_campaigns.name', 'like', "%$filters->search%");
            });
        }

        return $query;
    }
}
