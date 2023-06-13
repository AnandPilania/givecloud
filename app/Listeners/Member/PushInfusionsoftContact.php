<?php

namespace Ds\Listeners\Member;

use Ds\Events\AccountCreated;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class PushInfusionsoftContact implements ShouldQueue
{
    use Queueable;

    /**
     * Handle the event.
     *
     * @param \Ds\Events\AccountCreated $event
     * @return void
     */
    public function handle(AccountCreated $event)
    {
        // if infusionsoft is installed and there are tags to push, push them
        if (sys_get('infusionsoft_token')) {
            app('Ds\Services\InfusionsoftService')->pushAccount($event->account);
        }
    }

    public function viaQueue()
    {
        return 'low';
    }
}
