<?php

namespace Tests\Feature\Domain\FeaturePreviews;

use Ds\Domain\FeaturePreviews\FeaturePreviewsService;
use Ds\Models\User;
use Illuminate\Support\Facades\Config;
use Tests\Fakes\FakeFeaturePreviewCardWithCustomKey;
use Tests\TestCase;

/**
 * @group feature_previews
 */
class EnabledFeatureControllerTest extends TestCase
{
    public function testNotLoggedInFeatureRedirects(): void
    {
        $this
            ->post(route('feature_previews.store', ['feature' => 'feature_inexisting']))
            ->assertRedirect();

        $this
            ->delete(route('feature_previews.destroy', ['feature' => 'feature_inexisting']))
            ->assertRedirect();
    }

    public function testNotFoundFeatureReturns404(): void
    {
        $this->actingAsUser();

        $this
            ->post(route('feature_previews.store', ['feature' => 'feature_inexisting']))
            ->assertNotFound();

        $this
            ->delete(route('feature_previews.destroy', ['feature' => 'feature_inexisting']))
            ->assertNotFound();
    }

    public function testCanEnableFeature(): void
    {
        $this->actingAs(User::factory()->create());

        $key = 'some_user_enabled_feature_preview_card';

        Config::set('feature-previews', [FakeFeaturePreviewCardWithCustomKey::class]);

        $feature = app(FeaturePreviewsService::class)->get('feature_' . $key);

        $this->assertFalse($feature->isEnabled());

        $this
            ->post(route('feature_previews.store', ['feature' => $feature->key()]))
            ->assertCreated();

        $this->assertTrue($feature->isEnabled());
    }

    public function testCanDisableFeature(): void
    {
        $this->actingAs(User::factory()->create());

        $key = 'some_user_enabled_feature_preview_card';

        Config::set('feature-previews', [FakeFeaturePreviewCardWithCustomKey::class]);

        $feature = app(FeaturePreviewsService::class)->get('feature_' . $key);

        $feature->enable();

        $this->assertTrue($feature->isEnabled());

        $this
            ->delete(route('feature_previews.store', ['feature' => $feature->key()]))
            ->assertNoContent();

        $this->assertTrue($feature->isDisabled());
    }
}
