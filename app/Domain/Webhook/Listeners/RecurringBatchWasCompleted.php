<?php

namespace Ds\Domain\Webhook\Listeners;

use Ds\Domain\Webhook\Jobs\DeliverContributionResourceHook;
use Ds\Domain\Webhook\Jobs\DeliverTransactionBatchResourceHook;
use Ds\Domain\Webhook\Services\HookService;
use Ds\Events\RecurringBatchCompleted;

class RecurringBatchWasCompleted
{
    protected HookService $hookService;

    public function __construct(HookService $hookService)
    {
        $this->hookService = $hookService;
    }

    public function handle(RecurringBatchCompleted $event): void
    {
        if ($this->hookService->shouldDeliver('contribution_paid')) {
            DeliverTransactionBatchResourceHook::dispatch('contribution_paid', $event->batch->transactions)
                ->delay(sys_get('webhook_order_paid_delay') ?: null);
        }

        if ($this->hookService->shouldDeliver('contributions_paid')) {
            $contribution = $event->batch->transactions->first()->contribution;

            DeliverContributionResourceHook::dispatch('contributions_paid', $contribution)
                ->delay(sys_get('webhook_contributions_paid_delay') ?: null);
        }
    }
}
