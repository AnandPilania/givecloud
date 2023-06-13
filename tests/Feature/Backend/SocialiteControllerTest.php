<?php

namespace Tests\Feature\Backend;

use Ds\Mail\SocialLoginConfirmation;
use Ds\Models\SocialIdentity;
use Ds\Models\User;
use Ds\Services\SocialLoginService;
use Exception;
use Illuminate\Foundation\Testing\WithFaker;
use Mail;
use SocialiteProviders\Manager\OAuth2\User as SocialiteUser;
use Tests\TestCase;

/**
 * @group SocialLogin
 */
class SocialiteControllerTest extends TestCase
{
    use WithFaker;

    public function testRedirectReturnsRedirectResponse(): void
    {
        $response = $this->get(route('backend.socialite.redirect', 'google'));

        $response->assertRedirect();

        $this->assertStringContainsString('https://accounts.google.com/o/oauth2/', $response->getContent());
    }

    /** @dataProvider callbackInvalidParamsDataProvider */
    public function testCallbackCanValidateRequest(array $invalidParams, array $errorKey): void
    {
        $this->get(route('backend.socialite.callback', $invalidParams))
            ->assertRedirect(route('backend.session.login'))
            ->assertSessionHasErrors($errorKey);
    }

    public function callbackInvalidParamsDataProvider(): array
    {
        return [
            [['state' => base64_encode(json_encode([
                'provider' => null,
                'site' => 'microsoft',
            ]))], ['provider']],

            [['state' => base64_encode(json_encode([
                'provider' => 'invalid_provider',
                'site' => 'microsoft',
            ]))], ['provider']],

            [['no_state' => base64_encode(json_encode([
            ]))], ['state', 'provider']],
        ];
    }

    public function testCallbackCatchesExceptionsAndRedirects(): void
    {
        $this->partialMock(SocialLoginService::class)->shouldReceive('fromIncomingUser')->andThrow(new Exception('An error occurred.'));
        $response = $this->get(route('backend.socialite.callback', $this->validStateParams()));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('_flashMessages.error', 'An error occurred, please try again');
    }

    public function testCallbackWithNoKnownUserRedirectsWithError(): void
    {
        $email = $this->faker->email;

        $socialUser = new SocialiteUser;
        $socialUser->map(['email' => $email]);

        $this->partialMock(SocialLoginService::class)->shouldReceive('incomingUser')->andReturn($socialUser);

        $this->get(route('backend.socialite.callback', $this->validStateParams()))
            ->assertRedirect(route('login'))
            ->assertSessionHas('_flashMessages.error', 'We couldn\'t find a user with the email provided : ' . $email);
    }

    public function testCallbackLogsInKnownAndConfirmedUser(): void
    {
        $user = User::factory()->has(SocialIdentity::factory()->confirmed())->create();

        $socialUser = new SocialiteUser;
        $socialUser->map(['email' => $user->email]);

        $this->partialMock(SocialLoginService::class)->shouldReceive('incomingUser')->andReturn($socialUser);

        $this->get(route('backend.socialite.callback', $this->validStateParams()))
            ->assertRedirect(route('backend.session.index'))
            ->assertSessionMissing('_flashMessages');

        $this->assertAuthenticatedAs($user);
    }

    public function testCallbackCreatesSocialIdentityAndSendsConfirmationEmail(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        $socialUser = new SocialiteUser;
        $socialUser->map(['email' => $user->email]);
        $socialUser->setToken($this->faker->uuid);

        $this->partialMock(SocialLoginService::class)->shouldReceive('incomingUser')->andReturn($socialUser);

        $this->get(route('backend.socialite.callback', $this->validStateParams()))
            ->assertRedirect(route('login'))
            ->assertSessionHas('_flashMessages.success', "To link your Google account, follow the confirmation link in the email that we sent to your address {$user->email}");

        $this->assertDatabaseHas('social_identities', [
            'provider_name' => 'google',
            'authenticatable_id' => $user->id,
            'authenticatable_type' => $user->getMorphClass(),
            'is_confirmed' => false,
        ]);

        Mail::assertSent(SocialLoginConfirmation::class);
    }

    public function testConfirmWithoutTokenCatchesErrorsAndRedirects(): void
    {
        $response = $this->get(route('backend.socialite.confirm', [
            'provider' => 'google',
            'token' => $this->faker->uuid,
        ]));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('_flashMessages.error', 'Cannot link account, please try again.');
    }

    public function testConfirmWithNoUserCatchesErrorsAndRedirects(): void
    {
        $token = $this->faker->uuid;

        cache()->put(
            sprintf('auth_session:%s', hash('sha256', $token)),
            5, // Inexisting user_id
        );

        $response = $this->get(route('backend.socialite.confirm', [
            'provider' => 'google',
            'token' => $this->faker->uuid,
        ]));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('_flashMessages.error', 'Cannot link account, please try again.');
    }

    public function testConfirmsFailsOnInexistingSocialIdentity(): void
    {
        $user = User::factory()->create();

        $token = $this->faker->uuid;

        cache()->put(
            sprintf('auth_session:%s', hash('sha256', $token)),
            $user->getAuthIdentifier(),
        );

        $this->get(route('backend.socialite.confirm', [
            'provider' => 'google',
            'token' => $token,
        ]))->assertNotFound();
    }

    public function testRevokeRevokesIdentity(): void
    {
        $this->partialMock(SocialLoginService::class)->shouldReceive('revoke')->andReturnTrue();

        $this->actingAsAdminUser()
            ->from(route('backend.profile'))
            ->get(route('backend.socialite.revoke', [
                'provider' => 'google',
            ]))->assertRedirect(route('backend.profile'))
            ->assertSessionHas('_flashMessages.success', 'Successfully revoked access from Google');
    }

    public function testConfirmsAndLogsUser(): void
    {
        $user = User::factory()->create();

        $user->socialIdentities()->save(
            new SocialIdentity([
                'provider_name' => 'google',
                'provider_id' => $this->faker->uuid,
            ])
        );

        $token = $this->faker->uuid;

        cache()->put(
            sprintf('auth_session:%s', hash('sha256', $token)),
            $user->getAuthIdentifier(),
        );

        $this->get(route('backend.socialite.confirm', [
            'provider' => 'google',
            'token' => $token,
        ]))
            ->assertRedirect(route('backend.session.index'))
            ->assertSessionHas('_flashMessages.success', 'Google account linked successfully.');

        $this->assertAuthenticatedAs($user);
    }

    private function validStateParams(): array
    {
        return [
            'state' => base64_encode(json_encode([
                'provider' => 'google',
                'site' => 'microsoft',
            ])),
        ];
    }
}
