<?php

namespace Tests\Feature\Backend\Api\Settings;

use Ds\Models\Media;
use Tests\TestCase;

/** @group ApiSettings */
class BrandingControllerTest extends TestCase
{
    public function testGetReturnsBrandingResource(): void
    {
        $this->actingAsAdminUser();

        $this->get(route('api.settings.branding.show'))
            ->assertJsonPath('data.org_logo', null)
            ->assertJsonPath('data.org_primary_color', '#2467CC');
    }

    public function testCanUpdateBranding(): void
    {
        $this->actingAsAdminUser();

        $media = Media::factory()->jpeg()->create();

        $this->patch(route('api.settings.branding.store'), [
            'org_logo' => $media->getKey(),
            'org_primary_color' => '#1234FF',
        ])->assertJsonPath('data.org_logo.id', $media->hashid)
            ->assertJsonPath('data.org_primary_color', '#1234FF');
    }
}
