<?php

namespace Tests\Feature\Backend;

use Ds\Domain\Commerce\Models\PaymentProvider;
use Illuminate\Support\Str;
use Tests\TestCase;

class OnboardingControllerTest extends TestCase
{
    public function testGetNMISetupSuccess()
    {
        PaymentProvider::factory()->nmi()->create([
            'config' => [
                'setup_link' => sprintf(
                    '%s:%d',
                    $token = strtolower(Str::random(9)),
                    $expires = now()->addHours(48)->format('U')
                ),
            ],
        ]);

        $this->get(route('backend.onboarding.get_nmi_setup', $token))
            ->assertOk()
            ->assertViewIs('onboarding.nmi-setup')
            ->assertViewHas('screen', 'setup');
    }

    public function testGetNMISetupNoNMIProvider()
    {
        $this->get(route('backend.onboarding.get_nmi_setup', 'some-fake-token'))
            ->assertOk()
            ->assertViewIs('onboarding.nmi-setup')
            ->assertViewHas('screen', 'invalid');
    }

    public function testPostNMISetupSuccess()
    {
        PaymentProvider::factory()->nmi()->create([
            'config' => [
                'setup_link' => sprintf(
                    '%s:%d',
                    $token = strtolower(Str::random(9)),
                    $expires = now()->addHours(48)->format('U')
                ),
            ],
        ]);

        $this->post(route('backend.onboarding.post_nmi_setup', $token), ['credential3' => Str::random(9)])
            ->assertOk()
            ->assertViewIs('onboarding.nmi-setup')
            ->assertViewHas('screen', 'done');
    }

    public function testPostNMISetupNoNMIProvider()
    {
        $this->post(route('backend.onboarding.post_nmi_setup', 'some-fake-token'))
            ->assertOk()
            ->assertViewIs('onboarding.nmi-setup')
            ->assertViewHas('screen', 'invalid');
    }
}
