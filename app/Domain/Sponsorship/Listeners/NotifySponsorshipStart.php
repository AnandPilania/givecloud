<?php

namespace Ds\Domain\Sponsorship\Listeners;

use Ds\Domain\Sponsorship\Events\SponsorWasStarted;

class NotifySponsorshipStart
{
    /**
     * Handle the event.
     *
     * @param \Ds\Domain\Sponsorship\Events\SponsorWasStarted $event
     * @return void
     */
    public function handle(SponsorWasStarted $event)
    {
        if ($event->option('do_not_send_email')) {
            return false;
        }

        $event->sponsor->notify('sponsorship_started');
    }
}
