<?php

namespace Ds\Domain\Webhook\Listeners;

use Ds\Domain\Webhook\Jobs\DeliverContributionResourceHook;
use Ds\Domain\Webhook\Services\HookService;
use Ds\Events\Event;

class ContributionRefunded
{
    /** @var \Ds\Domain\Webhook\Services\HookService */
    protected $hookService;

    public function __construct(HookService $hookService)
    {
        $this->hookService = $hookService;
    }

    /**
     * @param \Ds\Events\OrderWasRefunded|\Ds\Events\RecurringPaymentWasRefunded $event
     * @return void
     */
    public function handle(Event $event): void
    {
        if ($this->hookService->shouldDeliver('contribution_refunded')) {
            $contribution = $event->order->contribution ?? $event->transaction->contribution;

            DeliverContributionResourceHook::dispatch('contribution_refunded', $contribution)
                ->delay(sys_get('webhook_contribution_refunded_delay') ?: null);
        }
    }
}
