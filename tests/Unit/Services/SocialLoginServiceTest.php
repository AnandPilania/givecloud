<?php

namespace Tests\Unit\Services;

use Carbon\Carbon;
use Ds\Mail\SocialLoginConfirmation;
use Ds\Models\SocialIdentity;
use Ds\Models\User;
use Ds\Services\SocialLoginService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Tests\TestCase;

/**
 * @group SocialLogin
 */
class SocialLoginServiceTest extends TestCase
{
    use WithFaker;

    public function testFromIncomingUserThrowsExceptionWhenNotFound(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $socialUser = new \SocialiteProviders\Manager\OAuth2\User();
        $socialUser->map(['email' => $this->faker->email]);

        $this->partialMock(SocialLoginService::class)->shouldReceive('incomingUser')->andReturn($socialUser);

        $this->app->make(SocialLoginService::class)->fromIncomingUser('google');
    }

    public function testFromIncomingUserReturnsUser(): void
    {
        [$user, $socialUser] = $this->getUsers();

        $this->partialMock(SocialLoginService::class)->shouldReceive('incomingUser')->andReturn($socialUser);

        $returnedUser = $this->app->make(SocialLoginService::class)->fromIncomingUser('google');

        $this->assertSame($user->id, $returnedUser->id);
    }

    public function testFindOrCreateSocialIdentityCreatesIdentity(): void
    {
        $provider = 'google';

        [, $socialUser] = $this->getUsers();

        $this->partialMock(SocialLoginService::class)->shouldReceive('incomingUser')->andReturn($socialUser);

        $identity = $this->app->make(SocialLoginService::class)->findOrCreateSocialIdentity($provider);

        $this->assertSame($provider, $identity->provider_name);
        $this->assertSame($socialUser->getId(), $identity->provider_id);
    }

    public function testConfirmSocialIdentityConfirms(): void
    {
        [$user, ] = $this->linkedIdentitiesAndMock(false);

        $identity = $this->app->make(SocialLoginService::class)->confirmSocialIdentity('google', $user);

        $this->assertTrue($identity->is_confirmed);
    }

    public function testIncomingUserDoesNotCallServiceTwice(): void
    {
        [, $socialUser] = $this->getUsers();

        Socialite::shouldReceive('driver->stateless')->once()->andReturnSelf();
        Socialite::shouldReceive('driver->user')->once()->andReturn($socialUser);

        $this->app->make(SocialLoginService::class)->incomingUser('google');
        $this->app->make(SocialLoginService::class)->incomingUser('google');
    }

    public function testIncomingUserIsConfirmedReturnsFalseWhenUserNotFound(): void
    {
        [$user, $socialUser] = $this->getUsers();

        $user->delete();

        $this->partialMock(SocialLoginService::class)->shouldReceive('incomingUser')->andReturn($socialUser);

        $this->assertFalse($this->app->make(SocialLoginService::class)->incomingUserIsConfirmed('google'));
    }

    public function testIncomingUserIsConfirmedReturnsFalseWhenNotConfirmed(): void
    {
        [, $socialUser] = $this->linkedIdentitiesAndMock(false);

        $this->partialMock(SocialLoginService::class)->shouldReceive('incomingUser')->andReturn($socialUser);

        $this->assertFalse($this->app->make(SocialLoginService::class)->incomingUserIsConfirmed('google'));
    }

    public function testIncomingUserIsConfirmedReturnsConfirmed(): void
    {
        $this->linkedIdentitiesAndMock();

        $this->assertTrue($this->app->make(SocialLoginService::class)->incomingUserIsConfirmed('google'));
    }

    public function testLoginIncomingUserCallsLoginWithUserAndProvider(): void
    {
        $this->linkedIdentitiesAndMock();

        $this->app->make(SocialLoginService::class)->loginIncomingUser('google');
    }

    public function testLoginLogsUserAndTouchesTimestamp(): void
    {
        [$user, ] = $this->linkedIdentitiesAndMock(true, ['created_at' => Carbon::yesterday()]);

        $this->app->make(SocialLoginService::class)->login($user, 'googles');

        $this->assertAuthenticatedAs($user);

        $this->assertSame(Carbon::today()->toDateString(), $user->socialIdentities()->ofProvider('google')->first()->created_at->toDateString());
    }

    public function testRevokeDeletesSocialIdentity(): void
    {
        [$user, ] = $this->linkedIdentitiesAndMock();

        $this->assertDatabaseHas('social_identities', [
            'authenticatable_id' => $user->id,
            'authenticatable_type' => $user->getMorphClass(),
        ]);

        $this->app->make(SocialLoginService::class)->revoke('google', $user);

        $this->assertDatabaseMissing('social_identities', [
            'authenticatable_id' => $user->id,
            'authenticatable_type' => $user->getMorphClass(),
        ]);
    }

    public function testSocialIdentityIsConfirmedReturnsConfirmedStatus(): void
    {
        [$user, ] = $this->linkedIdentitiesAndMock();

        $this->assertTrue($this->app->make(SocialLoginService::class)->socialIdentityIsConfirmed($user, 'google'));
    }

    public function testSocialIdentityIsConfirmedReturnsUnConfirmedStatus(): void
    {
        $provider = 'google';

        [$user, $socialUser] = $this->getUsers();
        $this->partialMock(SocialLoginService::class)->shouldReceive('incomingUser')->andReturn($socialUser);

        $this->app->make(SocialLoginService::class)->findOrCreateSocialIdentity($provider);

        $this->assertFalse($this->app->make(SocialLoginService::class)->socialIdentityIsConfirmed($user, $provider));
    }

    public function testSendConfirmationsSendsConfirmationMailable(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $provider = 'google';

        $this->app->make(SocialLoginService::class)->sendConfirmation($user, $provider);

        Mail::assertSent(SocialLoginConfirmation::class);
    }

    public function testUserFromTokenReturnsNull()
    {
        $inexistantUserId = $this->faker->numberBetween(1);
        $token = Str::random(32);

        cache()->put(sprintf('auth_session:%s', hash('sha256', $token)), $inexistantUserId);

        $this->assertNull($this->app->make(SocialLoginService::class)->userFromToken($token));
    }

    public function testUserFromTokenReturnsUser()
    {
        $user = User::factory()->create();
        $token = Str::random(32);

        cache()->put(sprintf('auth_session:%s', hash('sha256', $token)), $user->id);
        $returnedUser = $this->app->make(SocialLoginService::class)->userFromToken($token);

        $this->assertSame($user->id, $returnedUser->id);
    }

    private function linkedIdentitiesAndMock(bool $confirmed = true, array $params = []): array
    {
        $provider = 'google';

        [$user, $socialUser] = $this->getUsers();

        $user->socialIdentities()->save(
            new SocialIdentity(array_merge([
                'provider_name' => $provider,
                'provider_id' => $this->faker->uuid,
                'is_confirmed' => $confirmed,
            ], $params))
        );

        $this->partialMock(SocialLoginService::class)->shouldReceive('incomingUser')->andReturn($socialUser);

        return [$user, $socialUser];
    }

    private function getUsers(): array
    {
        $user = User::factory()->create();

        $socialUser = new \SocialiteProviders\Manager\OAuth2\User();
        $socialUser->map([
            'id' => $this->faker->uuid,
            'email' => $user->email,
        ]);

        return [$user, $socialUser];
    }
}
