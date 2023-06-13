<?php

namespace Tests\Unit\Domain\FeaturePreviews\Models;

use Config;
use Ds\Domain\FeaturePreviews\FeaturePreviewsService;
use Ds\Models\User;
use Tests\Fakes\FakeFeaturePreviewCardWithCustomKey;
use Tests\TestCase;

class FeaturePreviewStateTest extends TestCase
{
    public function testCanEnableFeatureForUser(): void
    {
        Config::set('feature-previews', [FakeFeaturePreviewCardWithCustomKey::class]);

        $this->actingAsUser(User::factory()->create());

        $feature = app(FeaturePreviewsService::class)->get('feature_some_user_enabled_feature_preview_card');

        $feature->enable();

        $this->assertTrue($feature->isEnabledForUser());
        $this->assertTrue(feature($feature->unprefixedKey()));
    }

    public function testCanDisableFeatureForUser(): void
    {
        $this->actingAsUser(User::factory()->create());

        $feature = new FakeFeaturePreviewCardWithCustomKey;

        $feature->enable();

        $feature->disable();

        $this->assertTrue($feature->isDisabled());
        $this->assertFalse($feature->isEnabled());
        $this->assertFalse($feature->isEnabledForUser());
        $this->assertFalse(feature($feature->unprefixedKey()));
    }
}
