<?php

namespace Ds\Domain\Webhook;

use Ds\Domain\Webhook\Listeners\AccountCreated;
use Ds\Domain\Webhook\Listeners\AccountUpdated;
use Ds\Domain\Webhook\Listeners\ContributionRefunded;
use Ds\Domain\Webhook\Listeners\OrderCompleted;
use Ds\Domain\Webhook\Listeners\OrderPaid;
use Ds\Domain\Webhook\Listeners\RecurringBatchWasCompleted;
use Ds\Domain\Webhook\Listeners\RecurringPaymentWasCompleted as RecurringPaymentWasCompletedListener;
use Ds\Events\AccountWasUpdated;
use Ds\Events\OrderWasCompleted;
use Ds\Events\OrderWasRefunded;
use Ds\Events\RecurringBatchCompleted;
use Ds\Events\RecurringPaymentWasCompleted;
use Ds\Events\RecurringPaymentWasRefunded;
use Ds\Providers\DomainEventServiceProviderInterface;

class HookEventServiceProvider implements DomainEventServiceProviderInterface
{
    public static function listens(): array
    {
        return [
            OrderWasCompleted::class => [
                OrderCompleted::class,
                OrderPaid::class,
            ],
            OrderWasRefunded::class => [
                ContributionRefunded::class,
            ],
            \Ds\Events\AccountCreated::class => [
                AccountCreated::class,
            ],
            AccountWasUpdated::class => [
                AccountUpdated::class,
            ],
            RecurringBatchCompleted::class => [
                RecurringBatchWasCompleted::class,
            ],
            RecurringPaymentWasCompleted::class => [
                RecurringPaymentWasCompletedListener::class,
            ],
            RecurringPaymentWasRefunded::class => [
                ContributionRefunded::class,
            ],
        ];
    }
}
