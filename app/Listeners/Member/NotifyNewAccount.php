<?php

namespace Ds\Listeners\Member;

use Ds\Events\AccountWasRegistered;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyNewAccount implements ShouldQueue
{
    use Queueable;

    /**
     * Handle the event.
     *
     * @param \Ds\Events\AccountWasRegistered $event
     * @return void
     */
    public function handle(AccountWasRegistered $event)
    {
        // send email notification
        member_notify_welcome($event->member->id);
    }
}
