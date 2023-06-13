<?php

namespace Tests\Feature\Backend\Api\Settings;

use Ds\Domain\Commerce\Models\PaymentProvider;
use Tests\TestCase;

/** @group ApiSettings */
class AcceptDonationsControllerTest extends TestCase
{
    public function testGetReturnsAcceptDonationResource(): void
    {
        $this->actingAsAdminUser();

        PaymentProvider::factory()->stripe()->create();

        $this->get(route('api.settings.accept-donations.show'))
            ->assertJsonStructure([
                'data' => [
                    'stripe' => [
                        'is_enabled',
                        'is_ach_allowed',
                        'is_wallet_pay_allowed',
                    ],
                    'paypal' => ['is_enabled'],
                    'venmo' => ['is_enabled'],
                ],
            ]);
    }

    public function testCanUpdateAcceptDonation(): void
    {
        $this->actingAsAdminUser();

        PaymentProvider::factory()->stripe()->create();

        $this->patch(route('api.settings.accept-donations.store'), [
            'is_wallet_pay_allowed' => true,
            'is_ach_allowed' => true,
        ])->assertJsonPath('data.stripe.is_wallet_pay_allowed', true)
            ->assertJsonPath('data.stripe.is_ach_allowed', true);
    }
}
