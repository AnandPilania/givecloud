<?php

namespace Ds\Domain\Webhook\Listeners;

use Ds\Domain\Webhook\Jobs\DeliverAccountHook;
use Ds\Domain\Webhook\Services\HookService;
use Ds\Events\AccountCreated as EventsAccountCreated;

class AccountCreated
{
    /** @var \Ds\Domain\Webhook\Services\HookService */
    protected $hookService;

    public function __construct(HookService $hookService)
    {
        $this->hookService = $hookService;
    }

    public function handle(EventsAccountCreated $event): void
    {
        if ($this->hookService->shouldDeliver('supporter_created')) {
            DeliverAccountHook::dispatch('supporter_created', $event->account)
                ->delay(sys_get('webhook_account_created_delay') ?: null);
        }
    }
}
