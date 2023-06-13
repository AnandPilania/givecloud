<?php

namespace Tests\Unit\Domain\Salesforce\Listeners;

use Ds\Domain\Salesforce\Listeners\RecurringPaymentWasCompleted as RecurringPaymentWasCompletedListener;
use Ds\Domain\Salesforce\Services\SalesforceContributionPaymentService;
use Ds\Domain\Salesforce\Services\SalesforceContributionService;
use Ds\Domain\Salesforce\Services\SalesforcePaymentsService;
use Ds\Domain\Salesforce\Services\SalesforceSupporterService;
use Ds\Domain\Salesforce\Services\SalesforceTransactionLineItemService;
use Ds\Domain\Salesforce\Services\SalesforceTransactionService;
use Ds\Events\RecurringPaymentWasCompleted;
use Ds\Models\RecurringBatch;
use Ds\Models\Transaction;
use Illuminate\Support\Facades\Bus;
use Tests\Concerns\InteractsWithRpps;
use Tests\TestCase;

/**
 * @group salesforce
 */
class RecurringPaymentWasCompletedTest extends TestCase
{
    use InteractsWithRpps;

    public function testReturnsEarlyIfRppIsPartOfBatch(): void
    {
        $this->mock(SalesforceTransactionService::class)->shouldNotReceive('upsert');

        [$rpp, $transaction] = $this->getRpp();
        $transaction->recurringBatch()->associate(RecurringBatch::factory()->create())->save();

        $event = new RecurringPaymentWasCompleted($rpp, $transaction);

        $this->assertNull($this->app->make(RecurringPaymentWasCompletedListener::class)->handle($event));
    }

    public function testUpsertsWhenRecurringPaymentIsCompleted(): void
    {
        $this->mock(SalesforceContributionService::class)->shouldNotReceive('upsert', 'upsertMultiple');

        $this->mock(SalesforceSupporterService::class)->shouldReceive('upsert')->once();
        $this->mock(SalesforceTransactionService::class)->shouldReceive('upsert')->once();
        $this->mock(SalesforceTransactionLineItemService::class)->shouldReceive('upsert')->once();
        $this->mock(SalesforcePaymentsService::class)->shouldReceive('upsertMultiple')->once();
        $this->mock(SalesforceContributionPaymentService::class)->shouldReceive('upsertMultiple')->once();

        $event = new RecurringPaymentWasCompleted(...$this->getRpp());

        $this->app->make(RecurringPaymentWasCompletedListener::class)->handle($event);
    }

    public function testShouldNotDispatchIfSalesforceIsDisabled(): void
    {
        Bus::fake();

        sys_set('salesforce_enabled', false);

        dispatch_sync(new RecurringPaymentWasCompleted(...$this->getRpp()));

        Bus::assertNotDispatched(RecurringPaymentWasCompletedListener::class);

        $this->assertFalse($this->app->make(RecurringPaymentWasCompletedListener::class)->shouldQueue());
    }

    public function testShouldNotDispatchIfSyncingSupportersIsDisable(): void
    {
        Bus::fake();

        sys_set('salesforce_enabled', true);

        $this->mock(SalesforceContributionService::class)->shouldReceive('shouldSync')->andReturnFalse();

        dispatch_sync(new RecurringPaymentWasCompleted(...$this->getRpp()));

        $this->assertFalse($this->app->make(RecurringPaymentWasCompletedListener::class)->shouldQueue());
        Bus::assertNotDispatched(RecurringPaymentWasCompletedListener::class);
    }

    private function getRpp(): array
    {
        return [
            $this->generateAccountsWithPMsAndRpps()->first()->recurringPaymentProfiles->first(),
            Transaction::factory()->create(),
        ];
    }
}
