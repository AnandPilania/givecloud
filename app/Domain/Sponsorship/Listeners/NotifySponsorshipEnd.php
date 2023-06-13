<?php

namespace Ds\Domain\Sponsorship\Listeners;

use Ds\Domain\Sponsorship\Events\SponsorWasEnded;

class NotifySponsorshipEnd
{
    /**
     * Handle the event.
     *
     * @param \Ds\Domain\Sponsorship\Events\SponsorWasEnded $event
     * @return void
     */
    public function handle(SponsorWasEnded $event)
    {
        if ($event->option('do_not_send_email')) {
            return false;
        }

        $event->sponsor->notify('sponsorship_ended');
    }
}
