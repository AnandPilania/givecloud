<?php

namespace Ds\Domain\HotGlue\Listeners\Salesforce;

use Ds\Domain\HotGlue\Listeners\AbstractHandler;
use Ds\Domain\HotGlue\Targets\AbstractTarget;
use Ds\Domain\HotGlue\Targets\SalesforceTarget;
use Ds\Domain\HotGlue\Transformers\Salesforce\AccountTransformer;
use Ds\Domain\HotGlue\Transformers\Salesforce\RecurringDonationTransformer;
use Ds\Events\Event;
use League\Fractal\Resource\Collection as FractalCollection;

class RecurringBatchCompleted extends AbstractHandler
{
    public function target(): AbstractTarget
    {
        return app(SalesforceTarget::class);
    }

    public function state(Event $event): array
    {
        $event->batch->transactions->loadMissing([
            'recurringPaymentProfile.member',
        ]);

        $transactionsCollection = new FractalCollection($event->batch->transactions->all(), new RecurringDonationTransformer, 'RecurringDonations');
        $transactionsState = app('fractal')->createArray($transactionsCollection);

        $accounts = $event->batch->transactions->pluck('recurringPaymentProfile.member')->unique('id');

        $accountsCollection = new FractalCollection($accounts, new AccountTransformer, 'Contacts');
        $accountsState = app('fractal')->createArray($accountsCollection);

        return array_merge(
            $accountsState,
            $transactionsState,
        );
    }

    public function shouldQueue(): bool
    {
        return sys_get('salesforce_recurring_donation_external_id')
            && parent::shouldQueue();
    }
}
