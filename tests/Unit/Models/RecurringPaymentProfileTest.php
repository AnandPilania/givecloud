<?php

namespace Tests\Unit\Models;

use Ds\Domain\Shared\Date;
use Ds\Domain\Shared\DateTime;
use Ds\Enums\BillingPeriod;
use Ds\Models\RecurringPaymentProfile;
use Illuminate\Support\Carbon;
use Tests\Concerns\InteractsWithRpps;
use Tests\TestCase;

class RecurringPaymentProfileTest extends TestCase
{
    use InteractsWithRpps;

    public function testManualChargeWithAmountEqualToZero()
    {
        $this->expectExceptionMessageMatches('/Can only charge amounts greater than zero/');

        $rpp = RecurringPaymentProfile::factory()->make(['amt' => 0]);
        $rpp->manualCharge('check');
    }

    public function testManualChargeWithAmountLessThanZero()
    {
        $this->expectExceptionMessageMatches('/Can only charge amounts greater than zero/');

        $rpp = RecurringPaymentProfile::factory()->make(['amt' => -25]);
        $rpp->manualCharge('check');
    }

    /**
     * @dataProvider firstPossibleStartDateProvider
     */
    public function testGetFirstPossibleStartDates(string $now, string $expecting, string $period, ?int $day, string $type, ?string $initialCharge, ?string $snapMethod)
    {
        $p = RecurringPaymentProfile::factory()->make();
        $p->billing_period = $period;

        sys_set("rpp_start_date_snap_{$period}", (string) $snapMethod);

        $date = $p->getFirstPossibleStartDate($type, $day, $day, $initialCharge, null, $now);

        $this->assertSameBillingDate($expecting, $date);
    }

    public function firstPossibleStartDateProvider(): array
    {
        return [
            ['2017-05-10', '2017-05-11', 'Day', null, 'natural', null, null],
            ['2017-05-10', '2017-05-17', 'Week', null, 'natural', null, null],
            ['2017-05-10', '2017-05-24', 'SemiMonth', null, 'natural', null, null],
            ['2017-05-10', '2017-06-10', 'Month', null, 'natural', null, null],
            ['2017-01-30', '2017-02-28', 'Month', null, 'natural', null, null],
            ['2017-05-31', '2017-06-30', 'Month', null, 'natural', null, null],
            ['2017-05-10', '2017-08-10', 'Quarter', null, 'natural', null, null],
            ['2017-05-10', '2017-11-10', 'SemiYear', null, 'natural', null, null],
            ['2017-05-10', '2018-05-10', 'Year', null, 'natural', null, null],
            ['2017-05-10', '2017-05-11', 'Day', null, 'fixed', null, null],
            ['2017-05-10', '2017-05-15', 'Week', 1, 'fixed', null, null],
            ['2017-05-10', '2017-05-17', 'Week', 3, 'fixed', null, null],
            ['2017-05-10', '2017-05-12', 'Week', 5, 'fixed', null, null],
            ['2017-05-10', '2017-05-14', 'Week', 7, 'fixed', null, null],
            ['2017-05-10', '2017-05-15', 'SemiMonth', 1, 'fixed', null, null],
            ['2017-05-10', '2017-05-17', 'SemiMonth', 3, 'fixed', null, null],
            ['2017-05-10', '2017-05-12', 'SemiMonth', 5, 'fixed', null, null],
            ['2017-05-10', '2017-06-01', 'Month', 1, 'fixed', null, null],
            ['2017-05-10', '2017-06-10', 'Month', 10, 'fixed', null, null],
            ['2017-05-10', '2017-05-25', 'Month', 25, 'fixed', null, null],
            ['2017-02-10', '2017-02-28', 'Month', 31, 'fixed', null, null],
            ['2017-05-10', '2017-06-01', 'Quarter', 1, 'fixed', null, null],
            ['2017-05-10', '2017-06-10', 'Quarter', 10, 'fixed', null, null],
            ['2017-05-10', '2017-05-25', 'Quarter', 25, 'fixed', null, null],
            ['2017-05-10', '2017-06-01', 'SemiYear', 1, 'fixed', null, null],
            ['2017-05-10', '2017-06-10', 'SemiYear', 10, 'fixed', null, null],
            ['2017-05-10', '2017-05-25', 'SemiYear', 25, 'fixed', null, null],
            ['2017-05-10', '2017-06-01', 'Year', 1, 'fixed', null, null],
            ['2017-05-10', '2017-06-10', 'Year', 10, 'fixed', null, null],
            ['2017-05-10', '2017-05-25', 'Year', 25, 'fixed', null, null],
            ['2017-05-10', '2017-05-11', 'Day', null, 'fixed', null, 'donor'],
            ['2017-05-10', '2017-05-15', 'Week', 1, 'fixed', null, 'donor'],
            ['2017-05-10', '2017-05-17', 'Week', 3, 'fixed', null, 'donor'],
            ['2017-05-10', '2017-05-12', 'Week', 5, 'fixed', null, 'donor'],
            ['2017-05-10', '2017-05-14', 'Week', 7, 'fixed', null, 'donor'],
            ['2017-05-10', '2017-05-15', 'SemiMonth', 1, 'fixed', null, 'donor'],
            ['2017-05-10', '2017-05-17', 'SemiMonth', 3, 'fixed', null, 'donor'],
            ['2017-05-10', '2017-05-12', 'SemiMonth', 5, 'fixed', null, 'donor'],
            ['2017-05-10', '2017-06-01', 'Month', 1, 'fixed', null, 'donor'],
            ['2017-05-10', '2017-06-10', 'Month', 10, 'fixed', null, 'donor'],
            ['2017-05-10', '2017-05-25', 'Month', 25, 'fixed', null, 'donor'],
            ['2017-02-10', '2017-02-28', 'Month', 31, 'fixed', null, 'donor'],
            ['2017-05-10', '2017-06-01', 'Quarter', 1, 'fixed', null, 'donor'],
            ['2017-05-10', '2017-06-10', 'Quarter', 10, 'fixed', null, 'donor'],
            ['2017-05-10', '2017-05-25', 'Quarter', 25, 'fixed', null, 'donor'],
            ['2017-05-10', '2017-06-01', 'SemiYear', 1, 'fixed', null, 'donor'],
            ['2017-05-10', '2017-06-10', 'SemiYear', 10, 'fixed', null, 'donor'],
            ['2017-05-10', '2017-05-25', 'SemiYear', 25, 'fixed', null, 'donor'],
            ['2017-05-10', '2017-06-01', 'Year', 1, 'fixed', null, 'donor'],
            ['2017-05-10', '2017-06-10', 'Year', 10, 'fixed', null, 'donor'],
            ['2017-05-10', '2017-05-25', 'Year', 25, 'fixed', null, 'donor'],
            ['2017-05-10', '2017-05-11', 'Day', null, 'fixed', 'one-time', 'donor'],
            ['2017-05-10', '2017-05-22', 'Week', 1, 'fixed', 'one-time', 'donor'],
            ['2017-05-10', '2017-05-17', 'Week', 3, 'fixed', 'one-time', 'donor'],
            ['2017-05-10', '2017-05-19', 'Week', 5, 'fixed', 'one-time', 'donor'],
            ['2017-05-10', '2017-05-21', 'Week', 7, 'fixed', 'one-time', 'donor'],
            ['2017-05-10', '2017-05-29', 'SemiMonth', 1, 'fixed', 'one-time', 'donor'],
            ['2017-05-10', '2017-05-24', 'SemiMonth', 3, 'fixed', 'one-time', 'donor'],
            ['2017-05-10', '2017-05-26', 'SemiMonth', 5, 'fixed', 'one-time', 'donor'],
            ['2017-05-10', '2017-07-01', 'Month', 1, 'fixed', 'one-time', 'donor'],
            ['2017-05-10', '2017-06-10', 'Month', 10, 'fixed', 'one-time', 'donor'],
            ['2017-05-10', '2017-06-25', 'Month', 25, 'fixed', 'one-time', 'donor'],
            ['2017-02-10', '2017-03-31', 'Month', 31, 'fixed', 'one-time', 'donor'],
            ['2017-05-10', '2017-09-01', 'Quarter', 1, 'fixed', 'one-time', 'donor'],
            ['2017-05-10', '2017-08-10', 'Quarter', 10, 'fixed', 'one-time', 'donor'],
            ['2017-05-10', '2017-08-25', 'Quarter', 25, 'fixed', 'one-time', 'donor'],
            ['2017-05-10', '2017-12-01', 'SemiYear', 1, 'fixed', 'one-time', 'donor'],
            ['2017-05-10', '2017-11-10', 'SemiYear', 10, 'fixed', 'one-time', 'donor'],
            ['2017-05-10', '2017-11-25', 'SemiYear', 25, 'fixed', 'one-time', 'donor'],
            ['2017-05-10', '2018-06-01', 'Year', 1, 'fixed', 'one-time', 'donor'],
            ['2017-05-10', '2018-05-10', 'Year', 10, 'fixed', 'one-time', 'donor'],
            ['2017-05-10', '2018-05-25', 'Year', 25, 'fixed', 'one-time', 'donor'],
            ['2017-05-10', '2017-05-11', 'Day', null, 'fixed', 'one-time', 'organization'],
            ['2017-05-10', '2017-05-15', 'Week', 1, 'fixed', 'one-time', 'organization'],
            ['2017-05-10', '2017-05-17', 'Week', 3, 'fixed', 'one-time', 'organization'],
            ['2017-05-10', '2017-05-12', 'Week', 5, 'fixed', 'one-time', 'organization'],
            ['2017-05-10', '2017-05-14', 'Week', 7, 'fixed', 'one-time', 'organization'],
            ['2017-05-10', '2017-05-15', 'SemiMonth', 1, 'fixed', 'one-time', 'organization'],
            ['2017-05-10', '2017-05-17', 'SemiMonth', 3, 'fixed', 'one-time', 'organization'],
            ['2017-05-10', '2017-05-12', 'SemiMonth', 5, 'fixed', 'one-time', 'organization'],
            ['2017-05-10', '2017-06-01', 'Month', 1, 'fixed', 'one-time', 'organization'],
            ['2017-05-10', '2017-06-10', 'Month', 10, 'fixed', 'one-time', 'organization'],
            ['2017-05-10', '2017-05-25', 'Month', 25, 'fixed', 'one-time', 'organization'],
            ['2017-02-10', '2017-02-28', 'Month', 31, 'fixed', 'one-time', 'organization'],
            ['2017-05-10', '2017-06-01', 'Quarter', 1, 'fixed', 'one-time', 'organization'],
            ['2017-05-10', '2017-06-10', 'Quarter', 10, 'fixed', 'one-time', 'organization'],
            ['2017-05-10', '2017-05-25', 'Quarter', 25, 'fixed', 'one-time', 'organization'],
            ['2017-05-10', '2017-06-01', 'SemiYear', 1, 'fixed', 'one-time', 'organization'],
            ['2017-05-10', '2017-06-10', 'SemiYear', 10, 'fixed', 'one-time', 'organization'],
            ['2017-05-10', '2017-05-25', 'SemiYear', 25, 'fixed', 'one-time', 'organization'],
            ['2017-05-10', '2017-06-01', 'Year', 1, 'fixed', 'one-time', 'organization'],
            ['2017-05-10', '2017-06-10', 'Year', 10, 'fixed', 'one-time', 'organization'],
            ['2017-05-10', '2017-05-25', 'Year', 25, 'fixed', 'one-time', 'organization'],
        ];
    }

    /**
     * @dataProvider nextBillingDateAcrossCycleProvider
     */
    public function testNextBillingDateAcrossCycles(
        string $orderDate,
        string $billingFrequency,
        int $billingCycleAnchor,
        string $type,
        array $expectedNextBillingDates
    ): void {
        sys_set(['rpp_default_type' => $type]);

        $account = $this->generateAccountWithPaymentMethods();

        DateTime::setTestNow(toUtc($orderDate));
        $rpp = $this->generateRpp($account, $account->defaultPaymentMethod, 1, 'USD', null, $billingFrequency, $billingCycleAnchor);
        DateTime::setTestNow();

        $expectedNextBillingDate = array_shift($expectedNextBillingDates);
        $this->assertSameBillingDate($expectedNextBillingDate, $rpp->next_billing_date);

        foreach ($expectedNextBillingDates as $expectedNextBillingDate) {
            DateTime::withTestNow($rpp->next_billing_date, function () use ($rpp) {
                $rpp->next_billing_date = $rpp->next_possible_billing_date;
            });

            $this->assertSameBillingDate($expectedNextBillingDate, $rpp->next_billing_date);
        }
    }

    public function nextBillingDateAcrossCycleProvider(): array
    {
        return [
            ['2020-12-31', 'monthly', 1, 'fixed', ['2021-01-01', '2021-02-01']],
            ['2020-12-31', 'monthly', 31, 'fixed', ['2021-01-31', '2021-02-28', '2021-03-31']],
            ['2020-12-01', 'monthly', 1, 'natural', ['2021-01-01', '2021-02-01']],
            ['2020-12-31', 'monthly', 31, 'natural', ['2021-01-31', '2021-02-28', '2021-03-31']],
        ];
    }

    private function assertSameBillingDate(string $expectedBillingDate, Date $billingDate): void
    {
        $this->assertTrue(
            Date::parse($expectedBillingDate)->equalTo($billingDate),
            sprintf('Was expecting %s but got %s.', $expectedBillingDate, $billingDate->format('Y-m-d'))
        );
    }

    /**
     * @dataProvider lastBillingDateValuesProvider
     */
    public function testLastBillingDateValues(string $billingPeriod, ?string $nextBillingDate, ?string $expecting): void
    {
        $rpp = new RecurringPaymentProfile([
            'profile_start_date' => fromUtc('2021-01-01'),
            'next_billing_date' => fromUtc($nextBillingDate),
            'billing_period' => $billingPeriod,
        ]);

        $this->assertSame($expecting, optional($rpp->last_billing_date)->toDateFormat());
    }

    public function lastBillingDateValuesProvider(): array
    {
        return [
            [BillingPeriod::DAY, '2021-02-01', '2021-01-31'],
            [BillingPeriod::WEEK, '2021-02-01', '2021-01-25'],
            [BillingPeriod::SEMI_MONTH, '2021-02-01', '2021-01-18'],
            [BillingPeriod::MONTH, null, null],
            [BillingPeriod::MONTH, '2021-01-01', null],
            [BillingPeriod::MONTH, '2021-02-01', '2021-01-01'],
            [BillingPeriod::QUARTER, '2021-04-01', '2021-01-01'],
            [BillingPeriod::SEMI_YEAR, '2021-07-01', '2021-01-01'],
            [BillingPeriod::YEAR, '2022-01-01', '2021-01-01'],
        ];
    }

    /** @dataProvider localizedPaymentStringDataProvider */
    public function testLocalizedPaymentStringIsLocalized(string $locale, $period, $expected): void
    {
        $this->app->setLocale($locale);

        $rpp = new RecurringPaymentProfile([
            'billing_period' => $period,
            'next_billing_date' => Carbon::parse('2021-10-11'),
            'amt' => 19.99,
            'currency_code' => 'USD',
        ]);

        $this->assertSame($expected, $rpp->payment_string);
    }

    public function localizedPaymentStringDataProvider(): array
    {
        return [
            ['en-CA', BillingPeriod::WEEK, '$19.99/Monday'],
            ['en-CA', BillingPeriod::MONTH, '$19.99/mth (11th)'],
            ['en-CA', BillingPeriod::QUARTER, '$19.99/qr (11th)'],
            ['en-CA', BillingPeriod::SEMI_MONTH, '$19.99/bi-Monday'],
            ['en-CA', BillingPeriod::SEMI_YEAR, '$19.99/6mth (11th)'],
            ['en-CA', BillingPeriod::YEAR, '$19.99/yr (11th)'],

            ['fr-CA', BillingPeriod::WEEK, '$19,99/lundi'],
            ['fr-CA', BillingPeriod::MONTH, '$19,99/mois (11)'],
            ['fr-CA', BillingPeriod::QUARTER, '$19,99/trim (11)'],
            ['fr-CA', BillingPeriod::SEMI_MONTH, '$19,99/deux-semaines (lundi)'],
            ['fr-CA', BillingPeriod::SEMI_YEAR, '$19,99/6mois (11)'],
            ['fr-CA', BillingPeriod::YEAR, '$19,99/an (11)'],

            ['es-MX', BillingPeriod::WEEK, '$19.99/lunes'],
            ['es-MX', BillingPeriod::MONTH, '$19.99/mes (11º)'],
            ['es-MX', BillingPeriod::QUARTER, '$19.99/tri (11º)'],
            ['es-MX', BillingPeriod::SEMI_MONTH, '$19.99/bi-lunes'],
            ['es-MX', BillingPeriod::SEMI_YEAR, '$19.99/6mes (11º)'],
            ['es-MX', BillingPeriod::YEAR, '$19.99/año (11º)'],
        ];
    }
}
