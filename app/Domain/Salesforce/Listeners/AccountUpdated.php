<?php

namespace Ds\Domain\Salesforce\Listeners;

use Ds\Domain\Salesforce\Services\SalesforceClientService;
use Ds\Domain\Salesforce\Services\SalesforceSupporterService;
use Ds\Events\AccountEventInterface as AccountEvent;
use Illuminate\Contracts\Queue\ShouldQueue;

class AccountUpdated implements ShouldQueue
{
    protected SalesforceSupporterService $salesforceSyncService;

    public function __construct(SalesforceSupporterService $salesforceSyncService)
    {
        $this->salesforceSyncService = $salesforceSyncService;
    }

    public function handle(AccountEvent $event): void
    {
        $this->salesforceSyncService->upsert($event->account);
    }

    public function shouldQueue(): bool
    {
        return app(SalesforceClientService::class)->isEnabled()
            && $this->salesforceSyncService->shouldSync();
    }
}
