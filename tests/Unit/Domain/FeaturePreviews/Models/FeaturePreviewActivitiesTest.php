<?php

namespace Tests\Unit\Domain\FeaturePreviews\Models;

use Ds\Domain\FeaturePreviews\Models\UserState;
use Ds\Models\User;
use Tests\TestCase;

class FeaturePreviewActivitiesTest extends TestCase
{
    public function testLogsActivities(): void
    {
        $this->actingAsUser(User::factory()->create());

        $state = UserState::factory()->create([
            'feature' => 'feature_test_mode',
        ]);

        $state->enabled = true;

        $state->save();

        $this->assertCount(1, $state->activities);
    }

    public function testLogsMultipleActivities(): void
    {
        $this->actingAsUser(User::factory()->create());

        $state = UserState::factory()->create(['feature' => 'feature_test_mode']);

        $state->enabled = true;
        $state->save();

        $state->enabled = false;
        $state->save();

        $this->assertCount(2, $state->activities);
    }
}
