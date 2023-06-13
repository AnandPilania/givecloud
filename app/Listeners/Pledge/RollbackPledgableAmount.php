<?php

namespace Ds\Listeners\Pledge;

use Ds\Events\PledgableAmountRollback;
use Ds\Events\PledgeDeleted;
use Illuminate\Contracts\Queue\ShouldQueue;

class RollbackPledgableAmount implements ShouldQueue
{
    public function handle(PledgeDeleted $event): void
    {
        event(new PledgableAmountRollback($event->pledge->campaign, $event->pledge));
    }
}
