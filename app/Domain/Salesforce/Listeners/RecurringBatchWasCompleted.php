<?php

namespace Ds\Domain\Salesforce\Listeners;

use Ds\Domain\Salesforce\Services\SalesforceClientService;
use Ds\Domain\Salesforce\Services\SalesforceContributionPaymentService;
use Ds\Domain\Salesforce\Services\SalesforcePaymentsService;
use Ds\Domain\Salesforce\Services\SalesforceSupporterService;
use Ds\Domain\Salesforce\Services\SalesforceTransactionLineItemService;
use Ds\Domain\Salesforce\Services\SalesforceTransactionService;
use Ds\Events\RecurringBatchCompleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;

class RecurringBatchWasCompleted implements ShouldQueue
{
    public function handle(RecurringBatchCompleted $event): void
    {
        $event->batch->transactions()->chunk(200, function (Collection $transactions) {
            $transactions->loadMissing([
                'recurringPaymentProfile.member',
                'recurringPaymentProfile.order_item',
                'recurringPaymentProfile.payments',
            ]);

            $accounts = $transactions->pluck('recurringPaymentProfile.member')->unique('id');

            $payments = $transactions->pluck('payments')->unique('id')->flatten();

            app(SalesforceSupporterService::class)->upsertMultiple($accounts);

            app(SalesforcePaymentsService::class)->upsertMultiple($payments);
            app(SalesforceTransactionService::class)->upsertMultiple($transactions);
            app(SalesforceContributionPaymentService::class)->upsertMultiple($payments->pluck('pivot'));

            app(SalesforceTransactionLineItemService::class)->upsertMultiple($transactions);
        });
    }

    public function shouldQueue(): bool
    {
        return app(SalesforceClientService::class)->isEnabled()
            && app(SalesforceTransactionService::class)->shouldSync();
    }
}
