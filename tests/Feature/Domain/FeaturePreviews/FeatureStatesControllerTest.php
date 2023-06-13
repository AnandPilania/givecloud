<?php

namespace Tests\Feature\Domain\FeaturePreviews;

use Ds\Domain\FeaturePreviews\FeaturePreviewsService;
use Ds\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\Fakes\FakeFeaturePreviewCardWithCustomKey;
use Tests\TestCase;

/**
 * @group feature_previews
 */
class FeatureStatesControllerTest extends TestCase
{
    public function testNotLoggedInFeaturesRedirects(): void
    {
        $this
            ->get(route('feature_previews.index'))
            ->assertRedirect();
    }

    public function testNoFeaturesReturnEmptyCollection(): void
    {
        Config::set('feature-previews', []);

        $this->actingAsUser();

        $this
            ->get(route('feature_previews.index'))
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function testReturnsFeatureInCollection(): void
    {
        Config::set('feature-previews', [FakeFeaturePreviewCardWithCustomKey::class]);

        $this->actingAs(User::factory()->create());

        $this
            ->get(route('feature_previews.index'))
            ->assertOk()
            ->assertJson(function (AssertableJson $json) {
                $json->has('data', 1, function (AssertableJson $json) {
                    $json->where('key', 'some_user_enabled_feature_preview_card')
                        ->where('enabled', false)
                        ->etc();
                });
            });
    }

    public function testReturnsEnabledFeature(): void
    {
        Config::set('feature-previews', [FakeFeaturePreviewCardWithCustomKey::class]);

        $this->actingAs(User::factory()->create());

        $feature = app(FeaturePreviewsService::class)->get('feature_' . 'some_user_enabled_feature_preview_card');
        $feature->enable();

        $this
            ->get(route('feature_previews.index'))
            ->assertOk()
            ->assertJson(function (AssertableJson $json) {
                $json->has('data', 1, function (AssertableJson $json) {
                    $json
                        ->where('name', 'feature_' . 'some_user_enabled_feature_preview_card')
                        ->where('key', 'some_user_enabled_feature_preview_card')
                        ->where('title', 'A title')
                        ->where('description', 'Lorem ipsum')
                        ->where('enabled', true)
                        ->has('links', function (AssertableJson $json) {
                            $json->where('A link', 'https://google.ca');
                        });
                });
            });
    }
}
