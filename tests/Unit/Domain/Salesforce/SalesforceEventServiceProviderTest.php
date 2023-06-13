<?php

namespace Tests\Unit\Domain\Salesforce;

use Ds\Domain\Salesforce\Listeners\AccountUpdated;
use Ds\Domain\Salesforce\Listeners\OrderPaid;
use Ds\Domain\Salesforce\Listeners\RecurringBatchWasCompleted;
use Ds\Domain\Salesforce\Listeners\RecurringPaymentWasCompleted as RecurringPaymentWasCompletedListener;
use Ds\Domain\Salesforce\SalesforceEventServiceProvider;
use Ds\Events\AccountCreated;
use Ds\Events\AccountWasUpdated;
use Ds\Events\OrderWasCompleted;
use Ds\Events\OrderWasRefunded;
use Ds\Events\RecurringBatchCompleted;
use Ds\Events\RecurringPaymentWasCompleted;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * @group salesforce
 */
class SalesforceEventServiceProviderTest extends TestCase
{
    public function testHasListeners(): void
    {
        $listeners = SalesforceEventServiceProvider::listens();

        $this->assertIsArray($listeners);

        $this->assertArrayHasKey(OrderWasCompleted::class, $listeners);
        $this->assertArrayHasKey(OrderWasRefunded::class, $listeners);
        $this->assertArrayHasKey(AccountCreated::class, $listeners);
        $this->assertArrayHasKey(AccountWasUpdated::class, $listeners);
        $this->assertArrayHasKey(RecurringPaymentWasCompleted::class, $listeners);
        $this->assertArrayHasKey(RecurringBatchCompleted::class, $listeners);
    }

    public function testListenersAreListening(): void
    {
        Event::fake();

        Event::assertListening(
            OrderWasCompleted::class,
            OrderPaid::class
        );
        Event::assertListening(
            OrderWasRefunded::class,
            OrderPaid::class
        );

        Event::assertListening(
            AccountCreated::class,
            AccountUpdated::class
        );

        Event::assertListening(
            AccountWasUpdated::class,
            AccountUpdated::class
        );

        Event::assertListening(
            RecurringPaymentWasCompleted::class,
            RecurringPaymentWasCompletedListener::class
        );

        Event::assertListening(
            RecurringBatchCompleted::class,
            RecurringBatchWasCompleted::class
        );
    }
}
