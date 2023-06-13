<?php

namespace Ds\Domain\Zapier\Listeners;

use Ds\Domain\Zapier\Jobs\AccountUpdatedTrigger;
use Ds\Events\AccountWasUpdated;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class AccountUpdatedListener implements ShouldQueue
{
    use Queueable;

    public function handle(AccountWasUpdated $event): void
    {
        AccountUpdatedTrigger::dispatch($event->account);
    }

    public function viaQueue()
    {
        return 'low';
    }
}
