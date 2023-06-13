<?php

namespace Ds\Domain\Webhook\Listeners;

use Ds\Domain\Webhook\Jobs\DeliverContributionResourceHook;
use Ds\Domain\Webhook\Jobs\DeliverOrderResourceHook;
use Ds\Domain\Webhook\Services\HookService;
use Ds\Events\OrderWasCompleted;

class OrderPaid
{
    /** @var \Ds\Domain\Webhook\Services\HookService */
    protected $hookService;

    public function __construct(HookService $hookService)
    {
        $this->hookService = $hookService;
    }

    public function handle(OrderWasCompleted $event): void
    {
        if ($this->hookService->shouldDeliver('contribution_paid')) {
            DeliverOrderResourceHook::dispatch('contribution_paid', $event->order)
                ->delay(sys_get('webhook_order_paid_delay') ?: null);
        }

        if ($this->hookService->shouldDeliver('contributions_paid')) {
            DeliverContributionResourceHook::dispatch('contributions_paid', $event->order->contribution)
                ->delay(sys_get('webhook_contributions_paid_delay') ?: null);
        }
    }
}
