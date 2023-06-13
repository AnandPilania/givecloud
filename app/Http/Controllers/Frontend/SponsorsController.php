<?php

namespace Ds\Http\Controllers\Frontend;

use Ds\Domain\Shared\Exceptions\MessageException;
use Ds\Domain\Sponsorship\Models\Sponsor;
use Ds\Domain\Sponsorship\Models\Sponsorship;
use Illuminate\Support\Facades\Redirect;

class SponsorsController extends Controller
{
    /**
     * Register controller middleware.
     */
    protected function registerMiddleware()
    {
        $this->middleware('auth.member', ['only' => [
            'index',
            'sponsorship',
        ]]);
    }

    /**
     * List all sponsorships that belong to the member.
     */
    public function index()
    {
        // prepare page
        pageSetup(__('frontend/accounts.sponsorships.index.my_sponsorships'), 'content');

        // grab the member with its active sponsorship data
        $member = \Ds\Models\Member::query()
            ->with([
                'sponsors' => function ($sponsors) {
                    $sponsors->active();
                },
                'sponsors.sponsorship',
            ])->where('id', member('id'))
            ->first();

        // render view
        return $this->renderTemplate('accounts/sponsorships/index', [
            'sponsors' => $member->sponsors,
        ]);
    }

    /**
     * View the details of a sponsors sponsorships
     */
    public function sponsorship($sponsor_id)
    {
        // grab the sponsorship based on this sponsor_id and the currently logged in member
        $sponsor = \Ds\Domain\Sponsorship\Models\Sponsor::with(['sponsorship.publicTimeline', 'sponsorship.segments.items'])
            ->where('member_id', member('id'))
            ->where('id', $sponsor_id)
            ->first();

        // if no member, return error 404
        if (! $sponsor) {
            abort(404, __('frontend/accounts.sponsorships.sponsorship_not_found'));
        }

        // prepare page
        pageSetup($sponsor->display_name, 'content');

        // render view
        return $this->renderTemplate('accounts/sponsorships/view', [
            'sponsorship' => $sponsor,
        ]);
    }

    /**
     * Edit the details of a sponsors sponsorships
     */
    public function editSponsorship($sponsor_id)
    {
        // grab the sponsorship based on this sponsor_id and the currently logged in member
        $sponsor = \Ds\Domain\Sponsorship\Models\Sponsor::where('member_id', member('id'))
            ->where('id', $sponsor_id)
            ->first();

        // if no member, return error 404
        if (! $sponsor) {
            abort(404, __('frontend/accounts.sponsorships.sponsorship_not_found'));
        }

        // prepare page
        pageSetup($sponsor->display_name, 'content');

        // render view
        return $this->renderTemplate('accounts/sponsorships/edit', [
            'sponsor' => $sponsor,
        ]);
    }

    /**
     * End a sponsors sponsorships
     */
    public function end($sponsor_id)
    {
        // if member doesn't have permission to end a sponsorship
        if (! sys_get('allow_member_to_end_sponsorship')) {
            throw new MessageException(__('frontend/accounts.sponsorships.not_allowed_to_end_sponsorship'));
        }

        // grab the sponsorship based on this sponsor_id and the currently logged in member
        $sponsor = Sponsor::where('member_id', member('id'))
            ->where('id', $sponsor_id)
            ->first();

        if (! $sponsor) {
            abort(404, __('frontend/accounts.sponsorships.sponsorship_not_found'));
        }

        $sponsor->ended_at = toUtc(request('ended_at')) ?? now();
        $sponsor->ended_reason = request('ended_reason');
        $sponsor->save();

        // send notification email that sponsorship was ended
        event(new \Ds\Domain\Sponsorship\Events\SponsorWasEnded($sponsor));

        return Redirect::action('Frontend\SponsorsController@index');
    }
}
