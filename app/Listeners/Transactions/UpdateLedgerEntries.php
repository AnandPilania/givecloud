<?php

namespace Ds\Listeners\Transactions;

use Ds\Events\RecurringPaymentEventInterface;
use Ds\Services\LedgerEntryService;

class UpdateLedgerEntries
{
    protected LedgerEntryService $ledgerEntryService;

    public function __construct(LedgerEntryService $ledgerEntryService)
    {
        $this->ledgerEntryService = $ledgerEntryService;
    }

    public function handle(RecurringPaymentEventInterface $event): void
    {
        $this->ledgerEntryService->make($event->transaction);
    }
}
