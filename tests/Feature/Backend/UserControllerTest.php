<?php

namespace Tests\Feature\Backend;

use Ds\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use WithFaker;

    public function testDisableTwoFactorAuth(): void
    {
        $user = User::factory()->twoFactorAuthentication()->create();

        $res = $this->actingAsAdminUser()->delete(route('backend.users.disable-two-factor-authentication', [$user->id]));

        $res->assertSessionHasFlashMessages(['success' => 'Two Factor Authentication disabled for user.']);

        $user->refresh();
        $this->assertNull($user->two_factor_secret);
        $this->assertNull($user->two_factor_recovery_codes);
    }

    public function testCanCreateUserWithoutPassword(): void
    {
        $email = $this->faker->email;

        $this->actingAsAdminUser()
            ->post(route('backend.users.insert'), [
                'firstname' => $this->faker->firstName,
                'lastname' => $this->faker->lastName,
                'email' => $email,
                'password' => null,
            ])->assertRedirect(route('backend.users.index'));

        $this->assertDatabaseHas('user', [
            'email' => $email,
        ]);
    }

    public function testValidatesPasswordWhenNotEmpty(): void
    {
        $email = $this->faker->email;

        $this->actingAsAdminUser()
            ->from(route('backend.users.add'))
            ->post(route('backend.users.insert'), [
                'firstname' => $this->faker->firstName,
                'lastname' => $this->faker->lastName,
                'email' => $email,
                'password' => 'tooshort',
            ])->assertSessionHasFlashMessages(['error' => 'Password must contain a combination of letters and numbers.']);

        $this->assertDatabaseMissing('user', [
            'email' => $email,
        ]);
    }
}
