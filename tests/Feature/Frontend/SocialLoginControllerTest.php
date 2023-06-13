<?php

namespace Tests\Feature\Frontend;

use Ds\Mail\SocialLoginNewProviderDetected;
use Ds\Models\Member;
use Ds\Models\SocialIdentity;
use Ds\Services\SupporterSocialLoginService;
use Exception;
use Illuminate\Support\Facades\Mail;
use SocialiteProviders\Manager\OAuth2\User as SocialUser;
use Tests\TestCase;

/**
 * @group SocialLogin
 * @group SupporterSocialLogin
 */
class SocialLoginControllerTest extends TestCase
{
    public function testRedirectReturnsRedirectResponse(): void
    {
        $response = $this->get(route('frontend.account.social.redirect', 'google'));

        $response->assertRedirect();
        $this->assertStringContainsString('https://accounts.google.com/o/oauth2/', $response->getContent());
    }

    public function testRedirectReturnsNotFoundIfProviderNotInList(): void
    {
        $response = $this->get(route('frontend.account.social.redirect', 'inexisting-provider'));

        $response->assertNotFound();
    }

    /** @dataProvider callbackInvalidParamsDataProvider */
    public function testCallbackValidatesRequest(array $invalidParams, array $errorKey): void
    {
        $this->get(route('frontend.account.social.callback', $invalidParams))
            ->assertRedirect(route('frontend.accounts.login'))
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
        $this->partialMock(SupporterSocialLoginService::class)->shouldReceive('fromIncomingUser')->andThrow(new Exception('An error occurred.'));
        $response = $this->get(route('frontend.account.social.callback', $this->validStateParams()));

        $response->assertRedirect(route('frontend.accounts.login'));
        $response->assertSessionHas('liquid_req.error', 'An error occurred, please try again');
    }

    public function testCallbackLogsInMemberAndSendEmailsWhenMemberExistsAndSocialIdentityIsNew(): void
    {
        Mail::fake();

        // Existing user
        $member = Member::factory()->has(SocialIdentity::factory())->create();
        $member->wasRecentlyCreated = false;

        // New social identity
        $socialUser = new SocialUser;
        $socialUser->map([
            'email' => $member->email,
            'user' => [],
        ]);

        $this->partialMock(SupporterSocialLoginService::class)->shouldReceive('incomingUser')->andReturn($socialUser);

        $this->get(route('frontend.account.social.callback', $this->validStateParams()))
            ->assertRedirect(route('frontend.accounts.home'))
            ->assertSessionMissing('liquid_req.error')
            ->assertSessionHas('liquid_req.success');

        $this->assertTrue(member_is_logged_in());

        Mail::assertSent(SocialLoginNewProviderDetected::class);
    }

    public function testDoesNotSendEmailWhenIdentityExists(): void
    {
        Mail::fake();

        $member = Member::factory()->create();
        $member->wasRecentlyCreated = false;

        $socialIdentity = SocialIdentity::factory()->for($member, 'authenticatable')->create();

        $socialUser = new SocialUser;
        $socialUser->map([
            'id' => $socialIdentity->provider_id,
            'email' => $member->email,
            'user' => [],
        ]);

        $this->partialMock(SupporterSocialLoginService::class)->shouldReceive('incomingUser')->andReturn($socialUser);

        $this->get(route('frontend.account.social.callback', $this->validStateParams()))
            ->assertRedirect(route('frontend.accounts.home'))
            ->assertSessionMissing('liquid_req.error');

        Mail::assertNotSent(SocialLoginNewProviderDetected::class);
    }

    public function testRevokeRevokesIdentityFromAccount(): void
    {
        $member = Member::factory()->create();
        $socialIdentity = SocialIdentity::factory()->for($member, 'authenticatable')->create();

        $this->assertDatabaseHas('social_identities', [
            'provider_name' => $socialIdentity->provider_name,
            'provider_id' => $socialIdentity->provider_id,
        ]);

        $this->actingAsAccount($member)
            ->from(route('accounts.profile'))
            ->get(route('frontend.account.social.revoke', [
                'provider' => 'google',
            ]))->assertRedirect(route('accounts.profile'))
            ->assertSessionHas('liquid_req.success');

        $this->assertDatabaseMissing('social_identities', [
            'provider_name' => $socialIdentity->provider_name,
            'provider_id' => $socialIdentity->provider_id,
        ]);
    }

    public function testCannotRevokeIdentityIfOnlyOneAndNoPasswordIsSet(): void
    {
        $member = Member::factory()->create(['password' => null]);
        $socialIdentity = SocialIdentity::factory()->for($member, 'authenticatable')->create();

        $this->assertDatabaseHas('social_identities', [
            'provider_name' => $socialIdentity->provider_name,
            'provider_id' => $socialIdentity->provider_id,
        ]);

        $this->assertNull($member->password);
        $this->assertCount(1, $member->socialIdentities->toArray());

        $this->actingAsAccount($member)
            ->from(route('accounts.profile'))
            ->get(route('frontend.account.social.revoke', [
                'provider' => 'google',
            ]))->assertRedirect(route('accounts.profile'))
            ->assertSessionHas('liquid_req.error');
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
