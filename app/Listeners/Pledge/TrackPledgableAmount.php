<?php

namespace Ds\Listeners\Pledge;

use Ds\Events\PledgableAmountCommitted;
use Ds\Events\PledgeCreated;
use Illuminate\Contracts\Queue\ShouldQueue;

class TrackPledgableAmount implements ShouldQueue
{
    public function handle(PledgeCreated $event): void
    {
        event(new PledgableAmountCommitted($event->pledge->campaign, $event->pledge));
    }
}
