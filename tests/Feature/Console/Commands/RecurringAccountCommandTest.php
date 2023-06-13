<?php

namespace Tests\Feature\Console\Commands;

use Ds\Domain\Shared\DateTime;
use Ds\Models\RecurringBatch;
use Tests\Concerns\InteractsWithRpps;
use Tests\TestCase;

/**
 * @group backend
 * @group console
 * @group rpp
 */
class RecurringAccountCommandTest extends TestCase
{
    use InteractsWithRpps;

    protected function tearDown(): void
    {
        DateTime::setTestNow();

        parent::tearDown();
    }

    public function testAccountNotFound()
    {
        $this->artisan('recurring:account', ['account_id' => null])
            ->expectsOutput('Supporter not found.')
            ->assertExitCode(1);
    }

    public function testNoChargeableRpps()
    {
        $account = $this->generateAccount();

        $this->artisan('recurring:account', ['account_id' => $account->getKey()])
            ->expectsOutput('No chargeable rpps.')
            ->assertExitCode(0);
    }

    /**
     * @dataProvider chargeableRppProvider
     */
    public function testChargeableRpp(string $now)
    {
        $account = $this->generateAccountWithPaymentMethods(1);

        DateTime::withTestNow(toUtc($now)->subMonthWithoutOverflow(), function () use ($account) {
            $this->generateRpps($account, $account->defaultPaymentMethod, 0, 'USD');
            $this->generateRpps($account, $account->defaultPaymentMethod, 1, 'USD');
        });

        DateTime::setTestNow(toUtc($now));

        $this->artisan('recurring:account', ['account_id' => $account->getKey()])
            ->expectsOutput(sprintf(
                'charging (1) rpps in [USD] using payment method (ID: %s)',
                $account->defaultPaymentMethod->getKey()
            ))->assertExitCode(0);

        $this->assertTrue($account->chargeableRpps()->doesntExist());
    }

    public function chargeableRppProvider(): array
    {
        // check today as well as dates with potential for month
        // overflow issues we've encountered with these tests in the past
        return [
            ['today'],
            ['2021-03-28'],
            ['2021-03-29'],
            ['2021-03-31'],
            ['2021-04-01'],
            ['2021-04-02'],
        ];
    }

    public function testChargeableRppsWithMultipleCurrencies()
    {
        $account = $this->generateAccountWithPaymentMethods(1);

        $this->generateRpps($account, $account->defaultPaymentMethod, 2, 'CAD');
        $this->generateRpps($account, $account->defaultPaymentMethod, 3, 'USD');

        $this->artisan('recurring:account', ['account_id' => $account->getKey()])
            ->expectsOutput(sprintf(
                'charging (2) rpps in [CAD] using payment method (ID: %s)',
                $account->defaultPaymentMethod->getKey()
            ))->expectsOutput(sprintf(
                'charging (3) rpps in [USD] using payment method (ID: %s)',
                $account->defaultPaymentMethod->getKey()
            ))->assertExitCode(0);

        $this->assertTrue($account->chargeableRpps()->doesntExist());
    }

    public function testChargeableRppsWithMultiplePaymentMethods()
    {
        $account = $this->generateAccountWithPaymentMethods(2);

        $this->generateRpps($account, $account->paymentMethods[0], 1, 'USD');
        $this->generateRpps($account, $account->paymentMethods[1], 2, 'USD');

        $this->artisan('recurring:account', ['account_id' => $account->getKey()])
            ->expectsOutput(sprintf(
                'charging (1) rpps in [USD] using payment method (ID: %s)',
                $account->paymentMethods[0]->getKey()
            ))->expectsOutput(sprintf(
                'charging (2) rpps in [USD] using payment method (ID: %s)',
                $account->paymentMethods[1]->getKey()
            ))->assertExitCode(0);

        $this->assertTrue($account->chargeableRpps()->doesntExist());
    }

    public function testChargeableRppsWithDifferentPlatformFees()
    {
        $account = $this->generateAccountWithPaymentMethods(2);

        $rpp = $this->generateRpp($account, $account->paymentMethods[0]);
        $rpp->platform_fee_type = 'migrated_profile';
        $rpp->save();

        $this->generateRpps($account, $account->paymentMethods[0], 2);

        $this->artisan('recurring:account', ['account_id' => $account->getKey()])
            ->expectsOutput(sprintf(
                'charging (1) rpps in [USD] using payment method (ID: %s)',
                $account->paymentMethods[0]->getKey()
            ))->expectsOutput(sprintf(
                'charging (2) rpps in [USD] using payment method (ID: %s)',
                $account->paymentMethods[0]->getKey()
            ))->assertExitCode(0);

        $this->assertTrue($account->chargeableRpps()->doesntExist());
        $this->assertTrue($rpp->payments()->where('platform_fee_type', $rpp->platform_fee_type)->exists());
    }

    public function testChargeableRppWithBatch()
    {
        $batch = RecurringBatch::factory()->create();
        $account = $this->generateAccountWithPaymentMethods(1);

        $this->generateRpps($account, $account->defaultPaymentMethod, 1, 'USD');

        $this->artisan('recurring:account', ['account_id' => $account->getKey(), '--batch-id' => $batch->getKey()])
            ->expectsOutput(sprintf(
                'charging (1) rpps in [USD] using payment method (ID: %s)',
                $account->defaultPaymentMethod->getKey()
            ))->assertExitCode(0);

        $this->assertTrue($account->chargeableRpps()->doesntExist());
        $this->assertSame(1, $batch->transactions()->count());
    }

    public function testChargeableRppsDryRun()
    {
        $account = $this->generateAccountWithPaymentMethods(2);

        $rpps = array_merge(
            $this->generateRpps($account, $account->paymentMethods[0], 1, 'CAD'),
            $this->generateRpps($account, $account->paymentMethods[1], 3, 'USD'),
            $this->generateRpps($account, $account->paymentMethods[1], 2, 'USD'),
        );

        $this->artisan('recurring:account', ['account_id' => $account->getKey(), '--dry-run' => true])
            ->expectsOutput(sprintf(
                '| %6d  %32s  %18s  %5s  %2d  %11s  %s',
                $account->getKey(),
                $account->display_name,
                $account->paymentMethods[0]->account_type,
                $account->paymentMethods[0]->account_last_four,
                1,
                money(10, 'CAD')->format('$0,0 $$$'),
                $rpps[0]->next_billing_date->format('Y-M-j')
            ))->expectsOutput(sprintf(
                '| %6d  %32s  %18s  %5s  %2d  %11s  %s',
                $account->getKey(),
                $account->display_name,
                $account->paymentMethods[1]->account_type,
                $account->paymentMethods[1]->account_last_four,
                5,
                money(50, 'USD')->format('$0,0 $$$'),
                $rpps[1]->next_billing_date->format('Y-M-j')
            ))->assertExitCode(0);

        $this->assertEquals($account->chargeableRpps()->count(), 6);
    }
}
