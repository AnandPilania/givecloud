<?php

namespace Ds\Domain\Webhook\Listeners;

use Ds\Domain\Webhook\Jobs\DeliverOrderHook;
use Ds\Domain\Webhook\Services\HookService;
use Ds\Events\OrderWasCompleted;

class OrderCompleted
{
    /** @var \Ds\Domain\Webhook\Services\HookService */
    protected $hookService;

    public function __construct(HookService $hookService)
    {
        $this->hookService = $hookService;
    }

    /**
     * Legacy order_completed event.
     *
     * @deprecated for OrderPaid
     */
    public function handle(OrderWasCompleted $event): void
    {
        if ($this->hookService->shouldDeliver('order_completed')) {
            DeliverOrderHook::dispatch('order_completed', $event->order)
                ->delay(sys_get('webhook_order_completed_delay') ?: null);
        }
    }
}
