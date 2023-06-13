<?php

namespace Tests\Unit\Domain\Salesforce\Services;

use Ds\Domain\Salesforce\SalesforceTokenStorage;
use Ds\Domain\Salesforce\Services\SalesforceClientService;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\RedirectResponse;
use Omniphx\Forrest\Exceptions\MissingTokenException;
use Omniphx\Forrest\Providers\Laravel\Facades\Forrest;
use Tests\TestCase;

/**
 * @group salesforce
 */
class SalesforceClientServiceTest extends TestCase
{
    public function testIsEnabledReturnsTrueWhenEnabled(): void
    {
        sys_set('feature_salesforce', true);
        sys_set('salesforce_enabled', true);

        $this->assertTrue($this->app->make(SalesforceClientService::class)->isEnabled());
    }

    public function testIsEnabledReturnsFalseWhenDisabled(): void
    {
        sys_set('feature_salesforce', true);
        sys_set('salesforce_enabled', false);

        $this->assertFalse($this->app->make(SalesforceClientService::class)->isEnabled());
    }

    public function testIsEnabledReturnsFalseWhenFeatureIsDisabled(): void
    {
        sys_set('feature_salesforce', false);
        sys_set('salesforce_enabled', true);

        $this->assertFalse($this->app->make(SalesforceClientService::class)->isEnabled());
    }

    public function testTokenReturnsToken(): void
    {
        $token = ['access_token' => 'some_token'];

        $storage = $this->app->make(SalesforceTokenStorage::class);
        $storage->put('token', encrypt($token));

        $returnedToken = $this->app->make(SalesforceClientService::class)->token();

        $this->assertSame($token, $returnedToken);
    }

    public function testTokenThrowsExceptionWhenNoToken(): void
    {
        $this->expectException(MissingTokenException::class);

        $this->app->make(SalesforceClientService::class)->token();
    }

    public function testHasTokenReturnsTrueWhenTokenAndEnabled(): void
    {
        sys_set('feature_salesforce', true);
        sys_set('salesforce_enabled', true);

        $storage = $this->app->make(SalesforceTokenStorage::class);
        $storage->put('token', encrypt(['access_token' => 'some_token']));

        $this->assertTrue($this->app->make(SalesforceClientService::class)->hasToken());
    }

    public function testHasTokenThrowsExceptionWhenNoToken(): void
    {
        sys_set('feature_salesforce', true);
        sys_set('salesforce_enabled', true);

        $this->assertFalse($this->app->make(SalesforceClientService::class)->hasToken());
    }

    public function testAuthenticateCallsUnderlyingService(): void
    {
        Forrest::shouldReceive('authenticate')->once()->andReturn(new RedirectResponse('/'));

        $this->app->make(SalesforceClientService::class)->authenticate();
    }

    public function testAuthenticatedEnablesSetting(): void
    {
        $this->assertFalse(sys_get('bool:salesforce_enabled'));

        $this->app->make(SalesforceClientService::class)->authenticated();

        $this->assertTrue(sys_get('bool:salesforce_enabled'));
    }

    public function testCallbackCallsUnderlyingServiceAndEnablesService(): void
    {
        Forrest::shouldReceive('callback')->once()->andReturn([]);

        $this->app->make(SalesforceClientService::class)->callback();

        $this->assertTrue(sys_get('bool:salesforce_enabled'));
    }

    public function testRevokeCallsUnderlyingServiceAndDisablesSalesforceInConfig(): void
    {
        Forrest::shouldReceive('revoke')->once();

        sys_set('feature_salesforce', true);
        sys_set('salesforce_enabled', true);

        $this->assertTrue(sys_get('bool:salesforce_enabled'));

        $this->app->make(SalesforceClientService::class)->revoke();

        $this->assertFalse(sys_get('bool:salesforce_enabled'));
    }

    /** @dataProvider revokeErrorMessagesDataProvider */
    public function testRevokeCatchesErrorAndDisablesSalesforceInConfigAndRemovesToken(string $message, bool $shouldCatchException): void
    {
        $service = Forrest::shouldReceive('revoke');
        $service->andThrow(new RequestException($message, new Request('POST', '/'), new Response(200, [], $message)));

        if (! $shouldCatchException) {
            $this->expectException(RequestException::class);
        }

        sys_set('feature_salesforce', true);
        sys_set('salesforce_enabled', true);

        $storage = $this->app->make(SalesforceTokenStorage::class);
        $storage->put('token', encrypt(['access_token' => 'some_token']));

        $this->app->make(SalesforceClientService::class)->revoke();

        $this->assertSame(! $shouldCatchException, sys_get('bool:salesforce_enabled'));
    }

    public function revokeErrorMessagesDataProvider(): array
    {
        return [
            ['error=unsupported_token_type', true],
            ['error=invalid_token', true],
            ['error=an_uncatched_error', false],
        ];
    }

    public function testTestCallsUnderlyingServiceAndReturnsTrue(): void
    {
        Forrest::shouldReceive('identity')->once()->andReturn([]);

        $this->assertTrue($this->app->make(SalesforceClientService::class)->test());
    }

    public function testTestCallsUnderlyingServiceAndReturnsFalseOnException(): void
    {
        Forrest::shouldReceive('identity')->once()->andThrow(MissingTokenException::class);

        $this->assertFalse($this->app->make(SalesforceClientService::class)->test());
    }

    public function testGetExceptionMessageReturnsMessageWhenNoResponse(): void
    {
        $message = 'An error occured';
        $this->assertSame(
            $message,
            $this->app->make(SalesforceClientService::class)->getExceptionMessage(
                new RequestException($message, new Request('GET', 'test'))
            )
        );
    }

    public function testGetExceptionMessageReturnsMessageFromResponseWhenHasResponse(): void
    {
        $responseBody = '{"error_description": "an error occured"}';

        $this->assertSame(
            'An error occured',
            $this->app->make(SalesforceClientService::class)->getExceptionMessage(
                new RequestException('different error message', new Request('GET', 'test'), new Response(200, [], $responseBody))
            )
        );
    }
}
