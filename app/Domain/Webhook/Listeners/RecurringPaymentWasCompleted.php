<?php

namespace Ds\Domain\Webhook\Listeners;

use Ds\Domain\Webhook\Jobs\DeliverContributionResourceHook;
use Ds\Domain\Webhook\Jobs\DeliverTransactionResourceHook;
use Ds\Domain\Webhook\Services\HookService;
use Ds\Events\RecurringPaymentWasCompleted as RecurringPaymentWasCompletedEvent;

class RecurringPaymentWasCompleted
{
    protected HookService $hookService;

    public function __construct(HookService $hookService)
    {
        $this->hookService = $hookService;
    }

    public function handle(RecurringPaymentWasCompletedEvent $event): void
    {
        // This is part of a batch, will send batch altogether.
        if ($event->transaction->recurring_batch_id) {
            return;
        }

        if ($this->hookService->shouldDeliver('contribution_paid')) {
            DeliverTransactionResourceHook::dispatch('contribution_paid', $event->transaction)
                ->delay(sys_get('webhook_order_paid_delay') ?: null);
        }

        if ($this->hookService->shouldDeliver('contributions_paid')) {
            // Ensure contribution is loaded.
            $event->transaction->refresh();

            DeliverContributionResourceHook::dispatch('contributions_paid', $event->transaction->contribution)
                ->delay(sys_get('webhook_contributions_paid_delay') ?: null);
        }
    }
}
