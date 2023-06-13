<?php

namespace Tests\Feature\Backend;

use Ds\Models\Passport\Client;
use Laravel\Passport\Passport;
use Tests\TestCase;

/**
 * @group zapier
 */
class ZapierSettingsControllerTest extends TestCase
{
    public function tearDown(): void
    {
        Passport::client()->truncate();

        parent::tearDown();
    }

    public function testShowPage()
    {
        $this
            ->actingAsUser($this->createUserWithPermissions('zapier.'))
            ->get(route('backend.settings.zapier.show'))
            ->assertOk()
            ->assertSee('Zapier');
    }

    public function testRedirectShowPageWhenNotAuthorized()
    {
        $this
            ->actingAsUser()
            ->get(route('backend.settings.zapier.show'))
            ->assertRedirect(route('backend.session.index'));
    }

    public function testEnableZapier()
    {
        sys_set('zapier_enabled', false);

        $this
            ->userVisitZapierSettingsPage()
            ->followingRedirects()
            ->post(route('backend.settings.zapier.store'), ['enabled' => true])
            ->assertSee('Zapier has been enabled.');

        $this->assertTrue((bool) sys_get('zapier_enabled'));
    }

    public function testDisableZapier()
    {
        sys_set('zapier_enabled', true);

        $this
            ->userVisitZapierSettingsPage()
            ->followingRedirects()
            ->post(route('backend.settings.zapier.store'))
            ->assertSee('Zapier has been disabled.');

        $this->assertFalse((bool) sys_get('zapier_enabled'));
    }

    public function testRedirectEnableZapierWhenNotAuthorized()
    {
        sys_set('zapier_enabled', false);

        $this
            ->userVisitZapierSettingsPage(null)
            ->followingRedirects()
            ->post(route('backend.settings.zapier.store'), ['enabled' => true])
            ->assertDontSee('Zapier has been enabled.');

        $this->assertFalse((bool) sys_get('zapier_enabled'));
    }

    private function userVisitZapierSettingsPage(?string $permissions = 'zapier.'): self
    {
        // A Zapier's Client is required to enable Zapier system wide.
        Passport::client()->create([
            'id' => Client::ZAPIER_CLIENT_ID,
            'name' => Client::ZAPIER_CLIENT_NAME,
        ]);

        $this
            ->actingAsUser($this->createUserWithPermissions($permissions))
            ->get(route('backend.settings.zapier.show'));

        return $this;
    }
}
