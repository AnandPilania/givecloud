<?php

namespace Ds\Listeners\Member;

use Ds\Events\AccountAddedToGroup;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class PushInfusionsoftTag implements ShouldQueue
{
    use Queueable;

    /**
     * Handle the event.
     *
     * @param \Ds\Events\AccountAddedToGroup $event
     * @return void
     */
    public function handle(AccountAddedToGroup $event)
    {
        // if infusionsoft is installed and there are tags to push, push them
        if (sys_get('infusionsoft_token') && $event->group_account->group->metadata->infusionsoft_tags) {
            app('Ds\Services\InfusionsoftService')->pushGroupAccount($event->group_account);
        }
    }

    public function viaQueue()
    {
        return 'low';
    }
}
