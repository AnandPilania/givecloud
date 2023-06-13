<?php

namespace Tests\Feature\Backend;

use Carbon\Carbon;
use Ds\Events\RecurringPaymentWasCompleted;
use Ds\Models\Member;
use Ds\Models\Membership;
use Ds\Models\Transaction;
use Tests\Concerns\InteractsWithRpps;
use Tests\TestCase;

class RecurringPaymentsControllerTest extends TestCase
{
    use InteractsWithRpps;

    public function testShowNotFoundProfileRedirectsBack(): void
    {
        $redirectBackUrl = route('backend.recurring_payments.index');

        $this->actingAsAdminUser()
            ->get($redirectBackUrl);

        $this->get(route('backend.recurring_payments.show', ['profile_id' => 0]))
            ->assertRedirect($redirectBackUrl);
    }

    /**
     * @dataProvider finalPaymentsDateDataProvider
     */
    public function testSaveEditsFinalPaymentDueDateReflectsBillingCyles(string $now, string $finalPaymentDate, string $expectedLastPaymentDue, int $expectedRemainingCycles): void
    {
        // Order is created a month ago.
        Carbon::setTestNow(fromLocal($now)->subMonthsWithoutOverflow());

        $account = $this->generateAccountWithPaymentMethods();
        $rpps = $this->generateRpps($account, $account->defaultPaymentMethod);
        $p = $rpps[0];

        Carbon::setTestNow($now);

        $this->actingAsAdminUser()
            ->post(route('backend.recurring_payments.profile.edit', ['profile_id' => $p->profile_id]), [
                'final_payment_due_date' => toUtc($finalPaymentDate),
                'amt' => $p->amt,
                'billing_period' => 'Month',
            ])->assertRedirect();

        $p->refresh();

        $this->assertSame($p->num_cycles_remaining, $expectedRemainingCycles);

        $this->assertTrue(
            Carbon::parse($p->final_billing_date)->eq(Carbon::parse($expectedLastPaymentDue)),
            sprintf('Was expecting %s but got %s.', $expectedLastPaymentDue, $p->final_billing_date->format('Y-m-d'))
        );

        Carbon::setTestNow();
    }

    public function finalPaymentsDateDataProvider(): array
    {
        // Now, FinalPaymentDate, Expected Final billing date, Cycles Remaining.
        return [
            ['2021-05-15', '2021-06-15', '2021-05-15', 1],
            ['2021-05-15', '2021-09-15', '2021-08-15', 4],

            ['2021-05-15', '2022-03-01', '2022-02-15', 10],
            ['2021-05-15', '2022-03-02', '2022-02-15', 10],
            ['2021-05-15', '2022-03-03', '2022-02-15', 10],
            ['2021-05-15', '2022-03-04', '2022-02-15', 10],
            ['2021-05-15', '2022-03-05', '2022-02-15', 10],
            ['2021-05-15', '2022-03-06', '2022-02-15', 10],
            ['2021-05-15', '2022-03-07', '2022-02-15', 10],
            ['2021-05-15', '2022-03-08', '2022-02-15', 10],
            ['2021-05-15', '2022-03-09', '2022-02-15', 10],
            ['2021-05-15', '2022-03-10', '2022-02-15', 10],
            ['2021-05-15', '2022-03-11', '2022-02-15', 10],
            ['2021-05-15', '2022-03-12', '2022-02-15', 10],
            ['2021-05-15', '2022-03-13', '2022-02-15', 10],
            ['2021-05-15', '2022-03-14', '2022-02-15', 10],
            ['2021-05-15', '2022-03-15', '2022-02-15', 10],
            ['2021-05-15', '2022-03-16', '2022-03-15', 11],
            ['2021-05-15', '2022-03-17', '2022-03-15', 11],
            ['2021-05-15', '2022-03-18', '2022-03-15', 11],
            ['2021-05-15', '2022-03-19', '2022-03-15', 11],
            ['2021-05-15', '2022-03-20', '2022-03-15', 11],
            ['2021-05-15', '2022-03-21', '2022-03-15', 11],
            ['2021-05-15', '2022-03-22', '2022-03-15', 11],
            ['2021-05-15', '2022-03-23', '2022-03-15', 11],
            ['2021-05-15', '2022-03-24', '2022-03-15', 11],
            ['2021-05-15', '2022-03-25', '2022-03-15', 11],
            ['2021-05-15', '2022-03-26', '2022-03-15', 11],
            ['2021-05-15', '2022-03-27', '2022-03-15', 11],
            ['2021-05-15', '2022-03-28', '2022-03-15', 11],
            ['2021-05-15', '2022-03-29', '2022-03-15', 11],
            ['2021-05-15', '2022-03-30', '2022-03-15', 11],

            ['2021-05-15', '2020-02-30', '2021-04-15', 0], // Last payment is before order, will return order date
        ];
    }

    /**
     * @dataProvider recurringPaymentMembershipExtensionDataProvider
     */
    public function testRecurringPaymentMembershipExtension(bool $setting_flag, string $initialStartDate, string $initialEndDate, string $finalStartDate, string $finalEndDate): void
    {
        // HACK: https://givecloud.atlassian.net/browse/ENG-293
        // Turn on Setting
        sys_set('force_recurring_payments_to_extend_memberships', $setting_flag);

        Carbon::setTestNow(
            Carbon::create(2021, 10, 31, 0, 0, 0, 'EST')
        );

        $account = $this->generateAccountWithPaymentMethods();
        $membership = Membership::factory()->create([
            'days_to_expire' => 365,
        ]);

        $rpps = $this->generateMembershipRpps($account, $account->defaultPaymentMethod, $membership);

        $membership = $account->groupAccountTimespans->first();

        $this->assertEquals($initialStartDate, $membership->pivot->start_date->format('Y-m-d'));
        $this->assertEquals($initialEndDate, $membership->pivot->end_date->format('Y-m-d'));

        $rpp = $rpps[0];
        $rpp->last_payment_date = Carbon::create(2022, 10, 31, 0, 0, 0, 'EST');

        event(new RecurringPaymentWasCompleted($rpp, Transaction::factory()->create()));

        $membership = Member::find($account->id)->groupAccountTimespans->first();

        $this->assertEquals($finalStartDate, $membership->pivot->start_date->format('Y-m-d'));
        $this->assertEquals($finalEndDate, $membership->pivot->end_date->format('Y-m-d'));
    }

    public function recurringPaymentMembershipExtensionDataProvider(): array
    {
        // Setting Flag (on/off), Initial Start Date, Initial End Date, Final Start Date, Final End Date.
        return [
            [true, '2021-10-31', '2022-10-31', '2021-10-31', '2023-10-31'],
            [false, '2021-10-31', '2022-10-31', '2021-10-31', '2022-10-31'],
        ];
    }

    /**  @dataProvider cancelReasonsDataProvioder */
    public function testCanUpdateCancelReason($reason)
    {
        $account = $this->generateAccountWithPaymentMethods();
        $rpps = $this->generateRpps($account, $account->defaultPaymentMethod);
        $rpp = $rpps[0];

        $rpp->updateCancelReason($reason);

        $this->assertSame($rpp->cancel_reason, $reason);
    }

    public function cancelReasonsDataProvioder(): array
    {
        return [
            [''],
            ["I'm no longer interested"],
            ["I can't afford it"],
            ["I've chosen a different charity"],
            ["I'd prefer not to say"],
        ];
    }
}
