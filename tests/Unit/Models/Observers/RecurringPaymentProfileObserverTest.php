<?php

namespace Tests\Unit\Models\Observers;

use Ds\Domain\Sponsorship\Services\SponsorCountService;
use Ds\Enums\RecurringPaymentProfileStatus;
use Ds\Models\RecurringPaymentProfile;
use Tests\Concerns\InteractsWithRpps;
use Tests\TestCase;

class RecurringPaymentProfileObserverTest extends TestCase
{
    use InteractsWithRpps;

    public function testSponsorshipCountUpdateWhenActivatingOnUpdated(): void
    {
        $rpp = $this->createSponsorshipRpp();

        $rpp->status = RecurringPaymentProfileStatus::SUSPENDED;
        $rpp->sponsor->ended_at = now();
        $rpp->save();

        $this->partialMock(SponsorCountService::class, function ($mock) {
            $mock->shouldReceive('update')->once();
        });

        $rpp->activateProfile();
    }

    public function testSponsorshipCountUpdateWhenSuspendingOnUpdated(): void
    {
        sys_set(['sponsorship_end_on_rpp_suspend' => true]);

        $rpp = $this->createSponsorshipRpp();

        $this->partialMock(SponsorCountService::class, function ($mock) {
            $mock->shouldReceive('update')->once();
        });

        $rpp->status = RecurringPaymentProfileStatus::SUSPENDED;
        $rpp->save();
    }

    public function testSponsorshipCountUpdateWhenCancellingOnUpdated(): void
    {
        sys_set(['sponsorship_end_on_rpp_cancel' => true]);

        $rpp = $this->createSponsorshipRpp();

        $this->partialMock(SponsorCountService::class, function ($mock) {
            $mock->shouldReceive('update')->once();
        });

        $rpp->cancelProfile();
    }

    private function createSponsorshipRpp(): RecurringPaymentProfile
    {
        $rpp = collect($this->generateSponsorshipRpps(
            $account = $this->generateAccountWithPaymentMethods(),
            $account->defaultPaymentMethod
        ))->first();

        return $rpp->load('sponsor');
    }
}
