<?php

namespace Ds\Domain\HotGlue\Targets;

use Ds\Domain\HotGlue\Listeners\Salesforce\AccountUpdated;
use Ds\Domain\HotGlue\Listeners\Salesforce\OrderPaid;
use Ds\Domain\HotGlue\Listeners\Salesforce\RecurringPaymentCompleted;
use Ds\Events\AccountCreated;
use Ds\Events\AccountWasUpdated;
use Ds\Events\OrderWasCompleted;
use Ds\Events\OrderWasRefunded;
use Ds\Events\RecurringBatchCompleted;
use Ds\Events\RecurringPaymentWasCompleted;

class SalesforceTarget extends AbstractTarget
{
    public string $name = 'salesforce';

    public function listens(): array
    {
        return [
            AccountCreated::class => [
                AccountUpdated::class,
            ],
            AccountWasUpdated::class => [
                AccountUpdated::class,
            ],
            OrderWasCompleted::class => [
                OrderPaid::class,
            ],
            OrderWasRefunded::class => [
                OrderPaid::class,
            ],
            RecurringPaymentWasCompleted::class => [
                RecurringPaymentCompleted::class,
            ],
            RecurringBatchCompleted::class => [
                RecurringPaymentCompleted::class,
            ],
        ];
    }
}
