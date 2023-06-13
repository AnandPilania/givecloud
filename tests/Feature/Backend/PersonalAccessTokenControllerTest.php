<?php

namespace Tests\Feature\Backend;

use Ds\Models\Passport\Token;
use Ds\Models\User;
use Ds\Services\PersonalAccessTokenService;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Tests\Concerns\InteractsWithPassport;
use Tests\TestCase;

/**
 * @group backend
 * @group api
 */
class PersonalAccessTokenControllerTest extends TestCase
{
    use InteractsWithPassport;

    public function testStoreSuccess(): void
    {
        $this
            ->userVisitsProfilePage()
            ->followingRedirects()
            ->post(route('backend.personal_access_tokens.store', ['name' => 'token name']))
            ->assertOk() // actually being redirected to previous page
            ->assertSee('Personal Access Token created');
    }

    public function testRedirectStoreWhenMissingName(): void
    {
        $this
            ->userVisitsProfilePage()
            ->post(route('backend.personal_access_tokens.store'), [])
            ->assertRedirect(route('backend.profile'));
    }

    public function testStoreShowMessageWhenCreateFails(): void
    {
        $errorMessage = 'error message to display';
        $personalAccessTokenServiceMock = $this->createMock(PersonalAccessTokenService::class);
        $personalAccessTokenServiceMock
            ->expects($this->once())
            ->method('create')
            ->willThrowException(new Exception($errorMessage));
        $personalAccessTokenServiceMock
            ->expects($this->exactly(2))
            ->method('getAllForUser')
            ->willReturn(new Collection());
        $this->instance(PersonalAccessTokenService::class, $personalAccessTokenServiceMock);

        $this
            ->userVisitsProfilePage()
            ->followingRedirects()
            ->post(route('backend.personal_access_tokens.store', ['name' => 'token name']))
            ->assertOk() // actually being redirected to previous page
            ->assertSee($errorMessage);
    }

    public function testRevokeSuccess(): void
    {
        $user = User::factory()->create();
        $personalAccessToken = Token::factory()->create(['user_id' => $user->getKey()]);

        $this
            ->userVisitsProfilePage($user)
            ->followingRedirects()
            ->delete(route('backend.personal_access_tokens.destroy', $personalAccessToken))
            ->assertOk() // actually being redirected to previous page
            ->assertSee("Personal Access Token $personalAccessToken->name has been successfully revoked");

        $this->assertTrue($personalAccessToken->refresh()->revoked);
    }

    public function testRedirectRevokeForOtherUserClient(): void
    {
        $personalAccessToken = Token::factory()->create();

        $this
            ->userVisitsProfilePage()
            ->delete(route('backend.personal_access_tokens.destroy', $personalAccessToken))
            ->assertRedirect(route('backend.profile'));

        $this->assertFalse($personalAccessToken->refresh()->revoked);
    }

    public function testRevokeShowMessageWhenRevokeFails(): void
    {
        $user = $this->createUserWithPermissions('personal_access_tokens.');
        $personalAccessToken = Token::factory()->create(['user_id' => $user->getKey()]);

        $personalAccessTokenServiceMock = $this->createMock(PersonalAccessTokenService::class);
        $personalAccessTokenServiceMock
            ->expects($this->once())
            ->method('revoke')
            ->willReturn(false);
        $personalAccessTokenServiceMock
            ->expects($this->exactly(2))
            ->method('getAllForUser')
            ->willReturn(new Collection());
        $this->instance(PersonalAccessTokenService::class, $personalAccessTokenServiceMock);

        $this
            ->userVisitsProfilePage($user)
            ->followingRedirects()
            ->delete(route('backend.personal_access_tokens.destroy', $personalAccessToken))
            ->assertOk() // actually being redirected to previous page
            ->assertSee('An error occured while revoking');
    }

    private function userVisitsProfilePage(?User $user = null): self
    {
        $this
            ->actingAsUser($user)
            ->get(route('backend.profile'));

        return $this;
    }
}
