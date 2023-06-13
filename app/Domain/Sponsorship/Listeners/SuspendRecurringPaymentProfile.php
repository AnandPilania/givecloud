<?php

namespace Ds\Domain\Sponsorship\Listeners;

use Ds\Domain\Sponsorship\Events\SponsorWasEnded;
use Ds\Enums\RecurringPaymentProfileStatus;

class SuspendRecurringPaymentProfile
{
    /**
     * Handle the event.
     *
     * @param \Ds\Domain\Sponsorship\Events\SponsorWasEnded $event
     */
    public function handle(SponsorWasEnded $event)
    {
        $rpp = $event->sponsor->recurringPaymentProfile;

        if ($rpp && $rpp->status === RecurringPaymentProfileStatus::ACTIVE) {
            $rpp->status = RecurringPaymentProfileStatus::SUSPENDED;
            $rpp->save();
        }
    }
}
