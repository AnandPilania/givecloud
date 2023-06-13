<?php

namespace Ds\Domain\HotGlue\Listeners\Salesforce;

use Ds\Domain\HotGlue\Listeners\AbstractHandler;
use Ds\Domain\HotGlue\Targets\AbstractTarget;
use Ds\Domain\HotGlue\Targets\SalesforceTarget;
use Ds\Domain\HotGlue\Transformers\Salesforce\AccountTransformer;
use Ds\Domain\HotGlue\Transformers\Salesforce\RecurringDonationTransformer;
use Ds\Events\Event;
use League\Fractal\Resource\Collection;

class RecurringPaymentCompleted extends AbstractHandler
{
    public function target(): AbstractTarget
    {
        return app(SalesforceTarget::class);
    }

    public function state(Event $event): array
    {
        $transaction = new Collection([$event->transaction], new RecurringDonationTransformer, 'RecurringDonations');
        $transactions = app('fractal')->createArray($transaction);

        $account = new Collection([$event->transaction->recurringPaymentProfile->member], new AccountTransformer, 'Contacts');
        $accounts = app('fractal')->createArray($account);

        return array_merge(
            $accounts,
            $transactions,
        );
    }

    public function shouldQueue(?Event $event = null): bool
    {
        // If transaction comes from batch, let it go, we'll treat it in RecurringBatchWasCompleted
        if (data_get($event, 'transaction.recurringBatch')) {
            return false;
        }

        return
            sys_get('salesforce_recurring_donation_external_id')
            && parent::shouldQueue();
    }
}
