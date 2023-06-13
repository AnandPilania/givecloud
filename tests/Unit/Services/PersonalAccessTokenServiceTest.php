<?php

namespace Tests\Unit\Services;

use Ds\Domain\Shared\Exceptions\MessageException;
use Ds\Models\Passport\Token;
use Ds\Models\User;
use Ds\Services\PersonalAccessTokenService;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Laravel\Passport\PersonalAccessTokenFactory;
use Laravel\Passport\PersonalAccessTokenResult;
use Tests\Concerns\InteractsWithPassport;
use Tests\TestCase;

/**
 * @group backend
 * @group services
 * @group api
 */
class PersonalAccessTokenServiceTest extends TestCase
{
    use InteractsWithPassport;

    public function testCreateSuccess(): void
    {
        $user = User::factory()->create();
        $name = 'some name';

        /** @var \Laravel\Passport\PersonalAccessTokenResult */
        $personalAccessTokenService = $this->app->make(PersonalAccessTokenService::class)->create($user, $name);

        $this->assertInstanceOf(PersonalAccessTokenResult::class, $personalAccessTokenService);
        $this->assertSame($user->getKey(), $personalAccessTokenService->token->user_id);
        $this->assertSame($name, $personalAccessTokenService->token->name);
        $this->assertFalse($personalAccessTokenService->token->revoked);
        $this->assertNotEmpty($personalAccessTokenService->accessToken);
    }

    /**
     * @dataProvider htmlInjectionsDataProvider
     */
    public function testCreateProtectAgainstHtmlInjectionsSuccess(string $inputName, string $expectedName): void
    {
        $user = User::factory()->create();

        /** @var \Laravel\Passport\PersonalAccessTokenResult */
        $personalAccessTokenService = $this->app->make(PersonalAccessTokenService::class)->create($user, $inputName);

        $this->assertInstanceOf(PersonalAccessTokenResult::class, $personalAccessTokenService);
        $this->assertSame($user->getKey(), $personalAccessTokenService->token->user_id);
        $this->assertSame($expectedName, $personalAccessTokenService->token->name);
        $this->assertFalse($personalAccessTokenService->token->revoked);
        $this->assertNotEmpty($personalAccessTokenService->accessToken);
    }

    public function htmlInjectionsDataProvider(): array
    {
        return [
            ['<a href="http://somelink.com">name</a>', 'name'],
            ['hey <script>alert("hack")</script>', 'hey alert("hack")'],
            ['<? phpinfo(); ?>', ''],
        ];
    }

    public function testCreateFailsThrowsException(): void
    {
        $user = User::factory()->create();
        $name = 'some name';

        $this->expectException(MessageException::class);
        $this->expectExceptionMessage("An error occured while creating $name personal access token.");

        /** @var \PHPUnit\Framework\MockObject\MockObject|\Laravel\Passport\PersonalAccessTokenFactory */
        $personalAccessTokenFactoryMock = $this->createMock(PersonalAccessTokenFactory::class);
        $personalAccessTokenFactoryMock
            ->expects($this->once())
            ->method('make')
            ->willThrowException(new Exception());
        $this->app->instance(PersonalAccessTokenFactory::class, $personalAccessTokenFactoryMock);

        $this->app->make(PersonalAccessTokenService::class)->create($user, $name);
    }

    public function testRevokeSuccess(): void
    {
        $token = Token::factory()->create();

        $this->assertTrue($this->app->make(PersonalAccessTokenService::class)->revoke($token));
    }

    public function testRevokeAlreadyRevoked(): void
    {
        $token = Token::factory()->revoked()->create();

        $this->assertTrue($this->app->make(PersonalAccessTokenService::class)->revoke($token));
    }

    public function testGetAllForUserSuccess(): void
    {
        $user = User::factory()->create();
        Token::factory()->create(['user_id' => 0]); // different user
        $userToken = Token::factory()->create(['user_id' => $user->getKey()])->refresh();

        $personalAccessTokens = $this->app->make(PersonalAccessTokenService::class)->getAllForUser($user->getKey());

        $this->assertInstanceOf(Collection::class, $personalAccessTokens);
        $this->assertEquals([$userToken->toArray()], $personalAccessTokens->toArray());
    }
}
