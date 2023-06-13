<?php

namespace Ds\Http\Controllers\Frontend;

use Ds\Events\VirtualEventReaction;
use Ds\Models\PledgeCampaign;
use Illuminate\Http\Request;

class VirtualEventReactionController extends Controller
{
    public function store(Request $request, PledgeCampaign $pledge_campaign)
    {
        $reaction = $request->input('reaction');
        event(new VirtualEventReaction($pledge_campaign, $reaction));

        return response()->noContent();
    }
}
