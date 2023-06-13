<?php

namespace Ds\Domain\Salesforce\Listeners;

use Ds\Domain\Salesforce\Services\SalesforceClientService;
use Ds\Domain\Salesforce\Services\SalesforceContributionPaymentService;
use Ds\Domain\Salesforce\Services\SalesforceContributionService;
use Ds\Domain\Salesforce\Services\SalesforceDiscountsService;
use Ds\Domain\Salesforce\Services\SalesforceLineItemService;
use Ds\Domain\Salesforce\Services\SalesforcePaymentsService;
use Ds\Domain\Salesforce\Services\SalesforceSupporterService;
use Ds\Events\Event;
use Illuminate\Contracts\Queue\ShouldQueue;

class OrderPaid implements ShouldQueue
{
    /**
     * @param \Ds\Events\OrderWasCompleted|\Ds\Events\OrderWasRefunded $event
     */
    public function handle(Event $event): void
    {
        if ($event->order->member) {
            app(SalesforceSupporterService::class)->upsert($event->order->member);
        }

        app(SalesforceContributionService::class)->upsert($event->order);

        if ($event->order->items->isNotEmpty()) {
            app(SalesforceLineItemService::class)->upsertMultiple($event->order->items);
        }

        if ($event->order->payments->isNotEmpty()) {
            app(SalesforcePaymentsService::class)->upsertMultiple($event->order->payments);
            app(SalesforceContributionPaymentService::class)->upsertMultiple($event->order->payments->pluck('pivot'));
        }

        if ($event->order->promoCodes->isNotEmpty()) {
            app(SalesforceDiscountsService::class)->upsertMultiple($event->order->promoCodes);
        }
    }

    public function shouldQueue(): bool
    {
        return app(SalesforceClientService::class)->isEnabled()
            && app(SalesforceContributionService::class)->shouldSync();
    }
}
