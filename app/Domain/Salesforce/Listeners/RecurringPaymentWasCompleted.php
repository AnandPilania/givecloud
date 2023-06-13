<?php

namespace Ds\Domain\Salesforce\Listeners;

use Ds\Domain\Salesforce\Services\SalesforceClientService;
use Ds\Domain\Salesforce\Services\SalesforceContributionPaymentService;
use Ds\Domain\Salesforce\Services\SalesforcePaymentsService;
use Ds\Domain\Salesforce\Services\SalesforceSupporterService;
use Ds\Domain\Salesforce\Services\SalesforceTransactionLineItemService;
use Ds\Domain\Salesforce\Services\SalesforceTransactionService;
use Ds\Events\RecurringPaymentWasCompleted as RecurringPaymentWasCompletedEvent;
use Illuminate\Contracts\Queue\ShouldQueue;

class RecurringPaymentWasCompleted implements ShouldQueue
{
    public function handle(RecurringPaymentWasCompletedEvent $event): void
    {
        // If transaction comes from batch, let it go, we'll treat it in RecurringBatchWasCompleted
        if ($event->transaction->recurringBatch) {
            return;
        }

        if ($event->rpp->member) {
            app(SalesforceSupporterService::class)->upsert($event->rpp->member);
        }

        app(SalesforcePaymentsService::class)->upsertMultiple($event->transaction->payments);
        app(SalesforceTransactionService::class)->upsert($event->transaction);
        app(SalesforceContributionPaymentService::class)->upsertMultiple($event->transaction->payments->pluck('pivot'));

        app(SalesforceTransactionLineItemService::class)->upsert($event->transaction);
    }

    public function shouldQueue(): bool
    {
        return app(SalesforceClientService::class)->isEnabled()
            && app(SalesforceTransactionService::class)->shouldSync();
    }
}
