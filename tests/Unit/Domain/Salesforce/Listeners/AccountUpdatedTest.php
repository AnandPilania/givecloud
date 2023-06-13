<?php

namespace Tests\Unit\Domain\Salesforce\Listeners;

use Ds\Domain\Salesforce\Listeners\AccountUpdated;
use Ds\Domain\Salesforce\Services\SalesforceSupporterService;
use Ds\Events\AccountWasUpdated;
use Ds\Models\Account;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

/**
 * @group salesforce
 */
class AccountUpdatedTest extends TestCase
{
    public function testUpsertsSupporterWhenAccountIsUpdated(): void
    {
        $this->mock(SalesforceSupporterService::class)->shouldReceive('upsert')->once();

        $event = new AccountWasUpdated(Account::factory()->create());

        $this->app->make(AccountUpdated::class)->handle($event);
    }

    public function testShouldNotDispatchIfSalesforceIsDisabled(): void
    {
        Bus::fake();

        sys_set('salesforce_enabled', false);

        dispatch_sync(new AccountWasUpdated(Account::factory()->create()));

        Bus::assertNotDispatched(AccountUpdated::class);
        $this->assertFalse($this->app->make(AccountUpdated::class)->shouldQueue());
    }

    public function testShouldNotDispatchIfSyncingSupportersIsDisable(): void
    {
        Bus::fake();

        sys_set('salesforce_enabled', true);

        $this->mock(SalesforceSupporterService::class)->shouldReceive('shouldSync')->andReturnFalse();

        dispatch_sync(new AccountWasUpdated(Account::factory()->create()));

        $this->assertFalse($this->app->make(AccountUpdated::class)->shouldQueue());
        Bus::assertNotDispatched(AccountUpdated::class);
    }
}
