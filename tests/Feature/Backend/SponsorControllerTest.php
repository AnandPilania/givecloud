<?php

namespace Tests\Feature\Backend;

use Ds\Enums\RecurringPaymentProfileStatus;
use Tests\Concerns\InteractsWithRpps;
use Tests\TestCase;

class SponsorControllerTest extends TestCase
{
    use InteractsWithRpps;

    /**
     * @dataProvider rppStatusFilterDataProvider
     */
    public function testRPPStatusFilter(array $statues, int $expectedRecords): void
    {
        $sponsorWithActiveRPP = $this->generateAccountWithPaymentMethods();
        $sponsorWithSuspendedRPP = $this->generateAccountWithPaymentMethods();
        $sponsorWithCancelledRPP = $this->generateAccountWithPaymentMethods();

        $activeRPP = collect($this->generateSponsorshipRpps($sponsorWithActiveRPP, $sponsorWithActiveRPP->defaultPaymentMethod))->first();
        $activeRPP->order_item->createSponsor();
        $activeRPP->refresh();
        $activeRPP->status = RecurringPaymentProfileStatus::ACTIVE;
        $activeRPP->save();

        $suspendedRPP = collect($this->generateSponsorshipRpps($sponsorWithSuspendedRPP, $sponsorWithSuspendedRPP->defaultPaymentMethod))->first();
        $suspendedRPP->order_item->createSponsor();
        $suspendedRPP->refresh();
        $suspendedRPP->status = RecurringPaymentProfileStatus::SUSPENDED;
        $suspendedRPP->save();

        $cancelledRPP = collect($this->generateSponsorshipRpps($sponsorWithCancelledRPP, $sponsorWithCancelledRPP->defaultPaymentMethod))->first();
        $cancelledRPP->order_item->createSponsor();
        $cancelledRPP->refresh();
        $cancelledRPP->status = RecurringPaymentProfileStatus::CANCELLED;
        $cancelledRPP->save();

        $filters = [
            'recurring_payments_status' => $statues,
        ];

        $this
            ->actingAsUser($this->createUserWithPermissions('sponsorship.view'))
            ->post(route('backend.sponsors.ajax'), $filters)
            ->assertOk()
            ->assertJsonCount($expectedRecords, 'data');
    }

    public function rppStatusFilterDataProvider(): array
    {
        return [
            [[RecurringPaymentProfileStatus::ACTIVE], 1],
            [[RecurringPaymentProfileStatus::ACTIVE, RecurringPaymentProfileStatus::SUSPENDED], 2],
            [[RecurringPaymentProfileStatus::ACTIVE, RecurringPaymentProfileStatus::SUSPENDED, RecurringPaymentProfileStatus::CANCELLED], 3],
        ];
    }
}
