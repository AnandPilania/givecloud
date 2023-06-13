<?php

namespace Tests\Feature\Backend;

use Ds\Domain\Settings\Integrations\Config\UPSIntegrationSettingsConfig;
use Ds\Domain\Settings\Integrations\Config\USPSIntegrationSettingsConfig;
use Ds\Domain\Settings\Integrations\Config\ZapierIntegrationSettingsConfig;
use Ds\Services\EmailService;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * @group backend
 * @group settings
 */
class SettingControllerTest extends TestCase
{
    public function testIntegrationsSeeIntegrations(): void
    {
        sys_set('zapier_enabled', false);

        $this
            ->actingAsUser()
            ->get(route('backend.settings.integrations'))
            ->assertOk()
            ->assertSeeText($this->app->make(ZapierIntegrationSettingsConfig::class)->name) // hidden via JavaScript
            ->assertSeeText($this->app->make(UPSIntegrationSettingsConfig::class)->name)
            ->assertSeeText($this->app->make(USPSIntegrationSettingsConfig::class)->name);
    }

    public function testIntegrationsSeeInstalledIntegrations(): void
    {
        sys_set('zapier_enabled', true);

        $response = $this
            ->actingAsUser()
            ->get(route('backend.settings.integrations'));

        $response
            ->assertOk()
            ->assertSeeText($this->app->make(ZapierIntegrationSettingsConfig::class)->name);

        $this->assertIntegrationsInstalled($response, 1);
    }

    public function testIntegrationsSeeNoInstalledIntegrations(): void
    {
        sys_set('zapier_enabled', false);

        $response = $this
            ->actingAsUser()
            ->get(route('backend.settings.integrations'));

        $response
            ->assertOk()
            ->assertSeeText($this->app->make(ZapierIntegrationSettingsConfig::class)->name);

        $this->assertIntegrationsInstalled($response);
    }

    /**
     * @dataProvider saveEmailSuccessDataProvider
     */
    public function testSaveEmailSuccess(?string $emailFromName = null, ?string $emailFrom = null, ?string $emailReplyTo = null): void
    {
        /** @var \Illuminate\Testing\TestResponse */
        $response = $this
            ->actingAsUser($this->createUserWithPermissions(['email.']))
            ->post(route('backend.settings.email_save'), [
                'email_from_name' => $emailFromName,
                'email_from_address' => $emailFrom,
                'email_replyto_address' => $emailReplyTo,
            ]);

        $response->assertRedirect(route('backend.settings.email'));
        $response->assertSessionHasFlashMessages(['success' => 'Email data successfully updated!']);
        $this->assertSame(trim($emailFromName) ?: config('sys.defaults.email_from_name'), sys_get('email_from_name'));
        $this->assertSame(trim($emailFrom) ?: 'notifications@givecloud.co', sys_get('email_from_address'));
        $this->assertSame(trim($emailReplyTo), sys_get('email_replyto_address'));
    }

    public function saveEmailSuccessDataProvider(): array
    {
        return [
            [], // fallback to 'notifications@givecloud.co'
            ['Notifications', 'notifications@givecloud.co'],
            ['To Trim', ' email@to-trim.com'],
            ['Notifications', 'notifications@givecloud.co', 'nico@gc.test'],
        ];
    }

    public function testSaveEmailWithInvalidFromAddressFailure(): void
    {
        /** @var \Illuminate\Testing\TestResponse */
        $response = $this
            ->actingAsUser($this->createUserWithPermissions(['email.']))
            ->post(route('backend.settings.email_save'), [
                'email_from_name' => 'invalid',
                'email_from_address' => 'invalid email@address',
                'email_replyto_address' => null,
            ]);

        $response->assertRedirect(route('backend.settings.email'));
        $response->assertSessionHasFlashMessages(['error' => 'Invalid From address.']);
    }

    public function testSaveEmailWithInvalidReplyToFailure(): void
    {
        $emailServiceMock = $this->createMock(EmailService::class);
        $emailServiceMock
            ->expects($this->once())
            ->method('hasSpfRestrictions')
            ->willReturn(false);
        $this->instance(EmailService::class, $emailServiceMock);

        /** @var \Illuminate\Testing\TestResponse */
        $response = $this
            ->actingAsUser($this->createUserWithPermissions(['email.']))
            ->post(route('backend.settings.email_save'), [
                'email_from_name' => 'Website',
                'email_from_address' => 'email@website.host',
                'email_replyto_address' => 'invalid email@address',
            ]);

        $response->assertRedirect(route('backend.settings.email'));
        $response->assertSessionHasFlashMessages(['error' => 'Invalid Reply-to address.']);
    }

    protected function assertIntegrationsInstalled(TestResponse $response, int $integrationsInstalledCount = 0): void
    {
        // API custom integration is always enabled.
        $integrationsInstalledCount++;

        // Depending on what happens before these tests, sometimes donorperfect is enabled.
        // In that case we should count for one more integration to show up.
        if (dpo_is_enabled()) {
            $integrationsInstalledCount++;
        }

        $this->assertSame($integrationsInstalledCount, substr_count(
            $response->getContent(),
            '<div class="flag flag-success"><i class="fa fa-check"></i> Installed</div>'
        ));
    }
}
