<?php

namespace Ds\Domain\Zapier\Listeners;

use Ds\Domain\Zapier\Jobs\AccountCreatedTrigger;
use Ds\Events\AccountCreated;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class AccountCreatedListener implements ShouldQueue
{
    use Queueable;

    public function handle(AccountCreated $event): void
    {
        AccountCreatedTrigger::dispatch($event->account);
    }

    public function viaQueue()
    {
        return 'low';
    }
}
