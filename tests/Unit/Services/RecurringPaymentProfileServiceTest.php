<?php

namespace Tests\Unit\Services;

use Ds\Enums\BillingPeriod;
use Ds\Models\RecurringPaymentProfile;
use Ds\Services\RecurringPaymentProfileService;
use Tests\Concerns\InteractsWithRpps;
use Tests\TestCase;

class RecurringPaymentProfileServiceTest extends TestCase
{
    use InteractsWithRpps;

    /**
     * @dataProvider nextPossibleBillingDateFromBillingDateInPastProvider
     */
    public function testNextPossibleBillingDateFromBillingDateInPast(string $billingPeriod, string $expecting): void
    {
        $rpp = new RecurringPaymentProfile([
            'profile_start_date' => '2021-01-01',
            'billing_period' => $billingPeriod,
        ]);

        $this->travelTo($rpp->profile_start_date);
        $this->travel(2)->months();

        $date = app(RecurringPaymentProfileService::class)->getNextPossibleBillingDate($rpp);

        $this->assertSame($expecting, $date->toDateFormat());
    }

    public function nextPossibleBillingDateFromBillingDateInPastProvider(): array
    {
        return [
            [BillingPeriod::WEEK, '2021-03-05'],
            [BillingPeriod::MONTH, '2021-03-01'],
        ];
    }

    public function testSoonestPossibleBillingDateFromBillingDateInPast(): void
    {
        $rpp = new RecurringPaymentProfile([
            'profile_start_date' => '2021-01-01',
            'billing_period' => BillingPeriod::YEAR,
        ]);

        $this->travelTo($rpp->profile_start_date);
        $this->travel(2)->months();

        $date = app(RecurringPaymentProfileService::class)->getSoonestPossibleBillingDate($rpp);

        $this->assertSame('2021-03-01', $date->toDateFormat());
    }
}
