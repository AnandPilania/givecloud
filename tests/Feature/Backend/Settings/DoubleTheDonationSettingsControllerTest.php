<?php

namespace Tests\Feature\Backend\Settings;

use Ds\Domain\DoubleTheDonation\DoubleTheDonationService;
use Ds\Domain\Shared\Exceptions\MessageException;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/** @group DoubleTheDonation */
class DoubleTheDonationSettingsControllerTest extends TestCase
{
    use WithFaker;

    public function testStoreCanStoreCredentialsAndEnableIntegration(): void
    {
        $private = $this->faker->uuid;
        $public = $this->faker->uuid;

        $this->actingAsAdminUser()
            ->post(route('backend.settings.integrations.double-the-donation.store'), [
                'double_the_donation_enabled' => true,
                'double_the_donation_public_key' => $public,
                'double_the_donation_private_key' => $private,
                'double_the_donation_sync_all_contributions' => false,
            ]);

        $this->assertSame($private, sys_get('double_the_donation_private_key'));
        $this->assertSame($public, sys_get('double_the_donation_public_key'));
        $this->assertFalse(sys_get('bool:double_the_donation_sync_all_contributions'));
        $this->assertTrue(sys_get('bool:double_the_donation_enabled'));
    }

    public function testCanDisableIntegration(): void
    {
        sys_set('double_the_donation_enabled', 1);

        $this->actingAsAdminUser()
            ->post(route('backend.settings.integrations.double-the-donation.store'), []);

        $this->assertFalse(sys_get('bool:double_the_donation_enabled'));
    }

    public function testCanTestConnection(): void
    {
        $this->mock(DoubleTheDonationService::class)->shouldReceive('test')->andReturnTrue();

        $this->actingAsAdminUser()
            ->post(route('backend.settings.integrations.double-the-donation.test'), [])
            ->assertSessionHas('_flashMessages.success', 'Connection to Double the Donation tested successfully');
    }

    public function testTestCanReturnErrorMessageFromService(): void
    {
        $message = 'Your public or private key is invalid';

        $this->mock(DoubleTheDonationService::class)->shouldReceive('test')->andThrow(new MessageException($message));

        $this->actingAsAdminUser()
            ->post(route('backend.settings.integrations.double-the-donation.test'), [])
            ->assertSessionHas('_flashMessages.error', $message);
    }
}
