<?php

namespace Tests\Unit\Domain\FeaturePreviews;

use Config;
use Ds\Domain\FeaturePreviews\FeaturePreviewsService;
use Tests\Fakes\FakeFeaturePreviewCard;
use Tests\TestCase;

class FeaturePreviewServiceTest extends TestCase
{
    public function testRegistersFeaturesFromConfig(): void
    {
        $feature = new FakeFeaturePreviewCard;

        Config::set('feature-previews', [FakeFeaturePreviewCard::class]);

        $key = $feature->key();

        $this->assertArrayHasKey($key, app(FeaturePreviewsService::class)->features()->toArray());
    }
}
