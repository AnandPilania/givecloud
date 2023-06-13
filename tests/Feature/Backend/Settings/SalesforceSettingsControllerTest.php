<?php

namespace Tests\Feature\Backend\Settings;

use Ds\Domain\Salesforce\Services\SalesforceClientService;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

/**
 * @group salesforce
 */
class SalesforceSettingsControllerTest extends TestCase
{
    use WithFaker;

    public function testIndexReturnsViewWithoutToken(): void
    {
        sys_set('feature_salesforce', true);

        $loginUrl = $this->faker->url;
        Config::set(['database.connections.soql.loginURL' => $loginUrl]);

        $response = $this->actingAsAdminUser()
            ->get(route('backend.settings.integrations.salesforce.legacy'))
            ->assertViewIs('settings.integrations.salesforce-legacy')
            ->assertViewHasAll([
                'loginUrl',
                'token',
            ]);

        $this->assertNull($response->viewData('token'));
        $this->assertSame($loginUrl, $response->viewData('loginUrl'));
    }

    public function testIndexReturnsViewWithTokenWhenSet(): void
    {
        sys_set('feature_salesforce', true);

        Config::set(['database.connections.soql.loginURL' => $this->faker->url]);
        $token = [
            'token' => $this->faker->uuid,
            'issued_at' => $this->faker->dateTime(),
        ];

        $mockedService = $this->mock(SalesforceClientService::class);
        $mockedService->shouldReceive('hasToken')->once()->andReturnTrue();
        $mockedService->shouldReceive('token')->once()->andReturn($token);

        $response = $this->actingAsAdminUser()
            ->get(route('backend.settings.integrations.salesforce.legacy'))
            ->assertViewIs('settings.integrations.salesforce-legacy')
            ->assertViewHasAll([
                'loginUrl',
                'token',
            ]);

        $this->assertSame($token, $response->viewData('token'));
    }

    public function testStoreStoresCredentialsSuccessfully(): void
    {
        $key = $this->faker->uuid;
        $secret = $this->faker->uuid;

        $this->assertEmpty(sys_get('salesforce_consumer_key'));
        $this->assertEmpty(sys_get('salesforce_consumer_secret'));

        $this->actingAsAdminUser()
            ->from(route('backend.settings.integrations.salesforce.legacy'))
            ->post(route('backend.settings.integrations.salesforce.store'), [
                'salesforce_consumer_key' => $key,
                'salesforce_consumer_secret' => $secret,
            ])->assertRedirect(route('backend.settings.integrations.salesforce.legacy'));

        $this->assertSame($key, sys_get('salesforce_consumer_key'));
        $this->assertSame($secret, sys_get('salesforce_consumer_secret'));
    }

    public function testConnectCallsUnderlyingService(): void
    {
        $this->mock(SalesforceClientService::class)->shouldReceive('authenticate')
            ->once()
            ->andReturn(new RedirectResponse(route('backend.settings.integrations.salesforce.legacy')));

        $this->actingAsAdminUser()
            ->from(route('backend.settings.integrations.salesforce.legacy'))
            ->get(route('backend.settings.integrations.salesforce.connect'))
            ->assertRedirect(route('backend.settings.integrations.salesforce.legacy'));
    }

    public function testCallbackCallsUnderlyingService(): void
    {
        $this->mock(SalesforceClientService::class)->shouldReceive('callback')->once()->andReturn([]);

        $this->actingAsAdminUser()
            ->from(route('backend.settings.integrations.salesforce.legacy'))
            ->get(route('backend.settings.integrations.salesforce.callback'))
            ->assertRedirect(route('backend.settings.integrations.salesforce.legacy'));
    }

    public function testTestCallsUnderlyingService(): void
    {
        $this->mock(SalesforceClientService::class)->shouldReceive('test')->once();

        $response = $this->actingAsAdminUser()
            ->from(route('backend.settings.integrations.salesforce.legacy'))
            ->get(route('backend.settings.integrations.salesforce.test'));

        $response->assertRedirect(route('backend.settings.integrations.salesforce.legacy'));
        $response->assertSessionHas('_flashMessages.success', 'Connection to Salesforce tested successfully');
    }

    public function testDisconnectCallsUnderlyingService(): void
    {
        $this->mock(SalesforceClientService::class)->shouldReceive('revoke')->once();

        $response = $this->actingAsAdminUser()
            ->from(route('backend.settings.integrations.salesforce.legacy'))
            ->get(route('backend.settings.integrations.salesforce.disconnect'));

        $response->assertRedirect(route('backend.settings.integrations.salesforce.legacy'));
        $response->assertSessionHas('_flashMessages.success', 'Token revoked successfully');
    }

    /**
     * @dataProvider methodsThatCatchesExceptionsDataProvider
     */
    public function testMethodsCanCatchExceptionsAndShowMessage(string $methodToMock, string $route): void
    {
        $exception = 'An error occured';

        $this->partialMock(SalesforceClientService::class)
            ->shouldReceive($methodToMock)
            ->once()
            ->andThrow(new RequestException($exception, new Request('GET', 'test')));

        $response = $this->actingAsAdminUser()
            ->from(route('backend.settings.integrations.salesforce.legacy'))
            ->get(route($route));

        $response->assertRedirect(route('backend.settings.integrations.salesforce.legacy'));
        $response->assertSessionHas('_flashMessages.error', $exception);
    }

    public function methodsThatCatchesExceptionsDataProvider(): array
    {
        return [
            ['callback', 'backend.settings.integrations.salesforce.callback'],
            ['revoke', 'backend.settings.integrations.salesforce.disconnect'],
            ['test', 'backend.settings.integrations.salesforce.test'],
        ];
    }
}
