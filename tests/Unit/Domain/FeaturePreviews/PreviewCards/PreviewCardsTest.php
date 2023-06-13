<?php

namespace Tests\Unit\Domain\FeaturePreviews\PreviewCards;

use Illuminate\Support\Str;
use Tests\Fakes\FakeFeaturePreviewCard;
use Tests\Fakes\FakeFeaturePreviewCardWithCustomKey;
use Tests\TestCase;

class PreviewCardsTest extends TestCase
{
    public function testFeatureHasValidKey()
    {
        $feature = new FakeFeaturePreviewCard;

        $key = Str::snake(class_basename($feature));

        $this->assertEquals('feature_' . $key, $feature->key());
        $this->assertEquals($key, $feature->unprefixedKey());
    }

    public function testFeatureWithCustomKey()
    {
        $feature = new FakeFeaturePreviewCardWithCustomKey;

        $this->assertEquals('feature_some_user_enabled_feature_preview_card', $feature->key());
        $this->assertEquals('some_user_enabled_feature_preview_card', $feature->unprefixedKey());
    }
}
