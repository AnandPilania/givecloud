<?php

namespace Tests\Unit\Domain\Salesforce\Listeners;

use Ds\Domain\Salesforce\Listeners\RecurringBatchWasCompleted;
use Ds\Domain\Salesforce\Services\SalesforceContributionPaymentService;
use Ds\Domain\Salesforce\Services\SalesforceContributionService;
use Ds\Domain\Salesforce\Services\SalesforcePaymentsService;
use Ds\Domain\Salesforce\Services\SalesforceSupporterService;
use Ds\Domain\Salesforce\Services\SalesforceTransactionLineItemService;
use Ds\Domain\Salesforce\Services\SalesforceTransactionService;
use Ds\Events\RecurringBatchCompleted;
use Ds\Models\Payment;
use Ds\Models\RecurringBatch;
use Ds\Models\Transaction;
use Illuminate\Support\Facades\Bus;
use Tests\Concerns\InteractsWithRpps;
use Tests\TestCase;

/**
 * @group salesforce
 */
class RecurringBatchWasCompletedTest extends TestCase
{
    use InteractsWithRpps;

    public function testUpsertsWhenRecurringPaymentIsCompleted(): void
    {
        $this->mock(SalesforceContributionService::class)->shouldNotReceive('upsert', 'upsertMultiple');

        $this->mock(SalesforceSupporterService::class)->shouldReceive('upsertMultiple')->once();
        $this->mock(SalesforceTransactionService::class)->shouldReceive('upsertMultiple')->once();
        $this->mock(SalesforceTransactionLineItemService::class)->shouldReceive('upsertMultiple')->once();
        $this->mock(SalesforcePaymentsService::class)->shouldReceive('upsertMultiple')->once();
        $this->mock(SalesforceContributionPaymentService::class)->shouldReceive('upsertMultiple')->once();

        $event = new RecurringBatchCompleted($this->batch());

        $this->app->make(RecurringBatchWasCompleted::class)->handle($event);
    }

    public function testShouldNotDispatchIfSalesforceIsDisabled(): void
    {
        Bus::fake();

        sys_set('salesforce_enabled', false);

        dispatch_sync(new RecurringBatchCompleted($this->batch()));

        Bus::assertNotDispatched(RecurringBatchWasCompleted::class);

        $this->assertFalse($this->app->make(RecurringBatchWasCompleted::class)->shouldQueue());
    }

    public function testShouldNotDispatchIfSyncingSupportersIsDisable(): void
    {
        Bus::fake();

        sys_set('salesforce_enabled', true);

        $this->mock(SalesforceContributionService::class)->shouldReceive('shouldSync')->andReturnFalse();

        dispatch_sync(new RecurringBatchCompleted($this->batch()));

        $this->assertFalse($this->app->make(RecurringBatchWasCompleted::class)->shouldQueue());
        Bus::assertNotDispatched(RecurringBatchWasCompleted::class);
    }

    private function batch(): RecurringBatch
    {
        $rpp = $this->generateAccountsWithPMsAndRpps()->first()->recurringPaymentProfiles->first();
        $payment = Payment::factory();
        $transactions = Transaction::factory()->paid()->count(3)->state([
            'recurring_payment_profile_id' => $rpp->id,
        ])->has($payment, 'payments');

        return RecurringBatch::factory()
            ->has($transactions)
            ->create();
    }
}
