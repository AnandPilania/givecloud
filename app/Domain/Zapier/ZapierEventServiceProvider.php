<?php

namespace Ds\Domain\Zapier;

use Ds\Domain\Zapier\Listeners\AccountCreatedListener;
use Ds\Domain\Zapier\Listeners\AccountUpdatedListener;
use Ds\Domain\Zapier\Listeners\OrderPaidListener;
use Ds\Domain\Zapier\Listeners\RecurringBatchWasCompleted;
use Ds\Events\AccountCreated;
use Ds\Events\AccountWasUpdated;
use Ds\Events\OrderWasCompleted;
use Ds\Events\RecurringBatchCompleted;
use Ds\Events\RecurringPaymentWasCompleted;
use Ds\Providers\DomainEventServiceProviderInterface;

class ZapierEventServiceProvider implements DomainEventServiceProviderInterface
{
    public static function listens(): array
    {
        if (! sys_get('zapier_enabled')) {
            return [];
        }

        return [
            AccountCreated::class => [
                AccountCreatedListener::class,
            ],
            AccountWasUpdated::class => [
                AccountUpdatedListener::class,
            ],
            OrderWasCompleted::class => [
                OrderPaidListener::class,
            ],
            RecurringPaymentWasCompleted::class => [
                Listeners\RecurringPaymentWasCompleted::class,
            ],
            RecurringBatchCompleted::class => [
                RecurringBatchWasCompleted::class,
            ],
        ];
    }
}
