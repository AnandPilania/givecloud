<?php

namespace Tests\Feature\Console\Commands;

use Carbon\Carbon;
use ChargeBee\ChargeBee\Models\Subscription as ChargeBeeSubscription;
use Ds\Domain\MissionControl\MissionControlService;
use Ds\Repositories\ChargebeeRepository;
use Tests\TestCase;

class BackfillChargebeePreferredPaymentCurrencyTest extends TestCase
{
    public function testBailsIfCurrencyIsNotCAD(): void
    {
        sys_set('dpo_currency', 'USD');

        $this->artisan('backfill:chargebee:currency')
            ->expectsOutput('Nothing to do here, currency is not CAD.')
            ->assertExitCode(0);
    }

    public function testBailsIfNoCustomerId(): void
    {
        sys_set('dpo_currency', 'CAD');

        site()->client->customer_id = null;

        $this->artisan('backfill:chargebee:currency')
            ->expectsOutput('No ChargeBee customer id, bailing.')
            ->assertExitCode(0);
    }

    public function testBailsIfActiveSubscription(): void
    {
        sys_set('dpo_currency', 'CAD');

        $subscription = $this->app->make(MissionControlService::class)->getSite()->subscription;
        $subscription->status = 'in trial';
        $subscription->trial_ends_on = Carbon::parse('next weeks');

        $subscription = new ChargeBeeSubscription(['id' => 'some_id']);

        $this->mock(ChargebeeRepository::class)->shouldReceive('getSubscription')->andReturn($subscription);

        $this->artisan('backfill:chargebee:currency')
            ->expectsOutput('Client has active subscription, can\'t touch this.')
            ->assertExitCode(0);
    }

    public function testBailsWhenInTrial(): void
    {
        sys_set('dpo_currency', 'CAD');

        $this->mock(ChargebeeRepository::class)->shouldReceive('getSubscription')->andReturnNull();
        $this->mock('chargebee')->shouldReceive('updateCustomer')->andReturnTrue();

        $this->artisan('backfill:chargebee:currency')
            ->expectsOutput('Client is not in trial, can\'t touch this.')
            ->assertExitCode(0);
    }

    public function testUpdatesChargebeeCustomersPreferredCurrency(): void
    {
        sys_set('dpo_currency', 'CAD');

        $this->mock(ChargebeeRepository::class)->shouldReceive('getSubscription')->andReturnNull();
        $this->mock('chargebee')->shouldReceive('updateCustomer')->andReturnTrue();

        $subscription = $this->app->make(MissionControlService::class)->getSite()->subscription;
        $subscription->status = 'in trial';
        $subscription->trial_ends_on = Carbon::parse('next weeks');

        $this->artisan('backfill:chargebee:currency')
            ->expectsOutput('Done.')
            ->assertExitCode(0);
    }
}
