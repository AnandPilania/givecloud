<?php

namespace Tests\Unit\Mail;

use Ds\Mail\SocialLoginConfirmation;
use Ds\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Concerns\InteractsWithMailables;
use Tests\TestCase;

/**
 * @group SocialLogin
 */
class SocialLoginConfirmationTest extends TestCase
{
    use InteractsWithMailables;
    use WithFaker;

    public function testMailablePreview(): void
    {
        $mailable = new SocialLoginConfirmation(User::factory()->create(), $this->faker->uuid, 'google');

        $this->assertMailablePreview($mailable);
    }

    public function testMailableHasConfirmationLink(): void
    {
        $provider = 'google';
        $token = $this->faker->uuid;

        $mailable = new SocialLoginConfirmation(User::factory()->create(), $token, $provider);

        $route = route('backend.socialite.confirm', [
            'provider' => $provider,
            'token' => $token,
        ]);

        $mailable->assertSeeInHtml($route);
    }
}
