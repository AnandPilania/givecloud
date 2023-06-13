<?php

namespace Ds\Domain\Sponsorship\Listeners;

use Ds\Domain\Sponsorship\Services\SponsorCountService;

class UpdateSponsorCount
{
    protected $sponsorCountService;

    public function __construct(SponsorCountService $sponsorCountService)
    {
        $this->sponsorCountService = $sponsorCountService;
    }

    /**
     * Handle the event.
     *
     * @param \Ds\Domain\Sponsorship\Events\SponsorWasStarted|\Ds\Domain\Sponsorship\Events\SponsorWasEnded $event
     */
    public function handle($event)
    {
        $this->sponsorCountService->update($event->sponsor->sponsorship);
    }
}
