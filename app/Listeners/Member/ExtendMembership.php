<?php

namespace Ds\Listeners\Member;

use Ds\Domain\Shared\Exceptions\MessageException;
use Ds\Services\DonorPerfectService;
use Throwable;

class ExtendMembership
{
    /** @var \Ds\Services\DonorPerfectService */
    protected $donorPerfectService;

    public function __construct(DonorPerfectService $donorPerfectService)
    {
        $this->donorPerfectService = $donorPerfectService;
    }

    /**
     * Handle the event.
     *
     * @param object $event
     * @return void
     */
    public function handle($event)
    {
        /*
            This is gross: it's a hack. And I'm sorry. - Kory
            This code should not be here in the first place.
            But if its here past September 2022 - then message me horrible things.
            https://givecloud.atlassian.net/browse/ENG-293
        */
        if (sys_get('force_recurring_payments_to_extend_memberships')) {
            $membershipItems = $event->rpp->order->items()
                ->whereHas('variant.membership')
                ->with('variant.membership')
                ->get();

            // loop over each item that has a membership
            $membershipItems->each(function ($item) use ($event) {
                // safely add the member to the group/membership
                try {
                    if (! $item->variant->membership) {
                        throw new MessageException('No membership exists on this contribution item.');
                    }

                    $member = $event->rpp->member;
                    $group = $item->variant->membership;
                    $member->addGroup($group, null, $item);

                    if (dpo_is_connected()) {
                        $member->refresh();
                        if ($member->membershipTimespan->pivot->group_id === $group->id) {
                            $this->donorPerfectService->updateDonorMembership($member->donor_id, $member->membershipTimespan->pivot);
                        }
                    }
                } catch (Throwable $e) {
                    // do nothing
                }
            });
        }
    }
}
