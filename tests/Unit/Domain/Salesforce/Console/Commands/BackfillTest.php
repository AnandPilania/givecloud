<?php

namespace Tests\Unit\Domain\Salesforce\Console\Commands;

use Ds\Domain\Salesforce\Services\SalesforceContributionPaymentService;
use Ds\Domain\Salesforce\Services\SalesforceContributionService;
use Ds\Domain\Salesforce\Services\SalesforceDiscountsService;
use Ds\Domain\Salesforce\Services\SalesforceLineItemService;
use Ds\Domain\Salesforce\Services\SalesforcePaymentsService;
use Ds\Domain\Salesforce\Services\SalesforceSupporterService;
use Ds\Domain\Salesforce\Services\SalesforceTransactionLineItemService;
use Ds\Domain\Salesforce\Services\SalesforceTransactionService;
use Ds\Models\Account;
use Ds\Models\Order;
use Ds\Models\OrderItem;
use Ds\Models\Payment;
use Ds\Models\PromoCode;
use Ds\Models\Transaction;
use Tests\Concerns\InteractsWithRpps;
use Tests\TestCase;

/**
 * @group salesforce
 */
class BackfillTest extends TestCase
{
    use InteractsWithRpps;

    /** @dataProvider artisanCommandsDataProvioder */
    public function testCommandWarnsWhenServiceNotEnabled(string $command, string $service): void
    {
        $this->artisan($command)
            ->expectsOutput('Salesforce is not enabled, not syncing.')
            ->assertExitCode(0);
    }

    /** @dataProvider artisanCommandsDataProvioder */
    public function testCommandCallsServiceWhenServiceIsEnabled(string $command, string $service): void
    {
        sys_set('salesforce_enabled', true);

        $account = Account::factory()->create();
        $promo = PromoCode::factory()->isFreeShipping()->create();
        $order = Order::factory()->paid()->hasAttached($promo)->create(['member_id' => $account]);
        $order->items()->saveMany(OrderItem::factory(3)->make());
        $order->payments()->saveMany(Payment::factory(2)->by($account)->paid()->create());

        $rpp = $this->generateAccountsWithPMsAndRpps()->first()->recurringPaymentProfiles->first();
        $transaction = Transaction::factory()->paid()->create();
        $transaction->recurringPaymentProfile()->associate($rpp)->save();

        $mock = $this->mock($service);
        $mock->shouldReceive('shouldSync')->andReturnTrue();
        $mock->shouldReceive('upsertMultiple')->andReturn([]);

        $this->artisan($command)
            ->assertExitCode(0);
    }

    public function artisanCommandsDataProvioder(): array
    {
        return [
            ['salesforce:backfill:contributions', SalesforceContributionService::class],
            ['salesforce:backfill:line-items', SalesforceLineItemService::class],
            ['salesforce:backfill:supporters', SalesforceSupporterService::class],
            ['salesforce:backfill:payments', SalesforcePaymentsService::class],
            ['salesforce:backfill:contribution-payments', SalesforceContributionPaymentService::class],
            ['salesforce:backfill:discounts', SalesforceDiscountsService::class],
            ['salesforce:backfill:transactions', SalesforceTransactionService::class],
            ['salesforce:backfill:transactions-line-items', SalesforceTransactionLineItemService::class],
        ];
    }

    public function testBackfillCallsOtherCommands(): void
    {
        sys_set('salesforce_enabled', true);

        $account = Account::factory()->create();
        $promo = PromoCode::factory()->isFreeShipping()->create();
        $order = Order::factory()->paid()->hasAttached($promo)->create(['member_id' => $account]);
        $order->items()->saveMany(OrderItem::factory(3)->make());
        $order->payments()->saveMany(Payment::factory(2)->by($account)->paid()->create());

        $rpp = $this->generateAccountsWithPMsAndRpps()->first()->recurringPaymentProfiles->first();
        $transaction = Transaction::factory()->paid()->create();
        $transaction->recurringPaymentProfile()->associate($rpp)->save();

        collect([
            SalesforceContributionService::class,
            SalesforceLineItemService::class,
            SalesforceSupporterService::class,
            SalesforcePaymentsService::class,
            SalesforceContributionPaymentService::class,
            SalesforceDiscountsService::class,
            SalesforceTransactionService::class,
            SalesforceTransactionLineItemService::class,
        ])->each(function ($service) {
            $mock = $this->mock($service);
            $mock->shouldReceive('shouldSync')->andReturnTrue();
            $mock->shouldReceive('upsertMultiple')->andReturn([]);
        });

        $this->artisan('salesforce:backfill')
            ->expectsOutput('Backfilling supporters.')
            ->expectsOutput('Backfilling contributions.')
            ->expectsOutput('Backfilling transactions.')
            ->expectsOutput('Backfilling line items.')
            ->expectsOutput('Backfilling transactions line items.')
            ->expectsOutput('Backfilling discounts.')
            ->expectsOutput('Backfilling payments.')
            ->expectsOutput('Backfilling contribution-payments.')
            ->assertExitCode(0);
    }
}
