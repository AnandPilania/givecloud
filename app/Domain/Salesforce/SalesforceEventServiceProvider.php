<?php

namespace Ds\Domain\Salesforce;

use Ds\Domain\Salesforce\Listeners\AccountUpdated;
use Ds\Domain\Salesforce\Listeners\OrderPaid;
use Ds\Domain\Salesforce\Listeners\RecurringBatchWasCompleted;
use Ds\Domain\Salesforce\Listeners\RecurringPaymentWasCompleted as RecurringPaymentWasCompletedListener;
use Ds\Events\AccountCreated;
use Ds\Events\AccountWasUpdated;
use Ds\Events\OrderWasCompleted;
use Ds\Events\OrderWasRefunded;
use Ds\Events\RecurringBatchCompleted;
use Ds\Events\RecurringPaymentWasCompleted;
use Ds\Providers\DomainEventServiceProviderInterface;

class SalesforceEventServiceProvider implements DomainEventServiceProviderInterface
{
    public static function listens(): array
    {
        return [
            OrderWasCompleted::class => [
                OrderPaid::class,
            ],
            OrderWasRefunded::class => [
                OrderPaid::class,
            ],
            AccountCreated::class => [
                AccountUpdated::class,
            ],
            AccountWasUpdated::class => [
                AccountUpdated::class,
            ],
            RecurringPaymentWasCompleted::class => [
                RecurringPaymentWasCompletedListener::class,
            ],
            RecurringBatchCompleted::class => [
                RecurringBatchWasCompleted::class,
            ],
        ];
    }
}
