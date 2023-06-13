<?php

namespace Ds\Domain\Webhook\Listeners;

use Ds\Domain\Webhook\Jobs\DeliverAccountHook;
use Ds\Domain\Webhook\Services\HookService;
use Ds\Events\AccountWasUpdated;

class AccountUpdated
{
    /** @var \Ds\Domain\Webhook\Services\HookService */
    protected $hookService;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(HookService $hookService)
    {
        $this->hookService = $hookService;
    }

    public function handle(AccountWasUpdated $event): void
    {
        if ($this->hookService->shouldDeliver('supporter_updated')) {
            DeliverAccountHook::dispatch('supporter_updated', $event->account)
                ->delay(sys_get('webhook_account_updated_delay') ?: null);
        }
    }
}
