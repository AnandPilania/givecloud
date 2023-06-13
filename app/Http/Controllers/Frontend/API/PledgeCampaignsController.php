<?php

namespace Ds\Http\Controllers\Frontend\API;

use Ds\Domain\Theming\Liquid\Drop;
use Ds\Events\PledgableAmountsRefresh;
use Ds\Events\PledgeCreated;
use Ds\Models\AccountType;
use Ds\Models\Member as Account;
use Ds\Models\Pledge;
use Ds\Models\PledgeCampaign;
use Illuminate\Http\Request;

class PledgeCampaignsController extends Controller
{
    /**
     * Register controller middleware.
     */
    protected function registerMiddleware()
    {
        $this->middleware('requires.feature:pledges');
    }

    /**
     * Create a pledge towards a campaign.
     *
     * @param \Ds\Models\PledgeCampaign $campaign
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createPledge(PledgeCampaign $campaign, Request $request)
    {
        $account = member();

        if (empty($account)) {
            $this->validate($request, [
                'first_name' => 'required',
                'last_name' => 'required',
                'email' => 'required|email',
            ]);

            $account = Account::findClosestMatchTo(['email' => request('email')]);

            if (empty($account)) {
                $account = Account::create([
                    'first_name' => request('first_name'),
                    'last_name' => request('last_name'),
                    'email' => request('email'),
                    'ship_first_name' => request('first_name'),
                    'ship_last_name' => request('last_name'),
                    'ship_email' => request('email'),
                    'ship_phone' => request('phone'),
                    'bill_first_name' => request('first_name'),
                    'bill_last_name' => request('last_name'),
                    'bill_email' => request('email'),
                    'bill_phone' => request('phone'),
                    'account_type_id' => AccountType::getDefault()->id,
                ]);
            }
        }

        $this->validate($request, [
            'amount' => 'required|numeric|gt:0',
            'currency_code' => 'required',
        ]);

        $pledge = Pledge::create([
            'account_id' => $account->id,
            'pledge_campaign_id' => $campaign->id,
            'total_amount' => request('amount'),
            'currency_code' => request('currency_code'),
            'is_anonymous' => (bool) request('is_anonymous'),
            'comments' => request('comments'),
        ]);

        $pledge->calculate(true);

        $account->notify('customer_pledge_received', $pledge->getMergeTags());

        event(new PledgeCreated($pledge));

        return $this->success([
            'pledge' => Drop::factory($pledge, 'Pledge'),
        ]);
    }

    /**
     * Broadcast a refresh for the pledge campaign.
     *
     * @param \Ds\Models\PledgeCampaign $campaign
     * @return \Illuminate\Http\JsonResponse
     */
    public function broadcastRefresh(PledgeCampaign $campaign)
    {
        event(new PledgableAmountsRefresh($campaign));

        return $this->success();
    }
}
