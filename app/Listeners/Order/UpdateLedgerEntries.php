<?php

namespace Ds\Listeners\Order;

use Ds\Events\OrderWasCompleted;
use Ds\Events\OrderWasRefunded;
use Ds\Services\LedgerEntryService;

class UpdateLedgerEntries
{
    /** @var \Ds\Services\LedgerEntryService */
    protected $ledgerEntryService;

    public function __construct(LedgerEntryService $ledgerEntryService)
    {
        $this->ledgerEntryService = $ledgerEntryService;
    }

    /**
     * Handle the event.
     *
     * @param object $event
     * @return void
     */
    public function handle($event)
    {
        if (! $event instanceof OrderWasRefunded && ! $event instanceof OrderWasCompleted) {
            return;
        }

        $this->ledgerEntryService->make($event->order);
    }
}
