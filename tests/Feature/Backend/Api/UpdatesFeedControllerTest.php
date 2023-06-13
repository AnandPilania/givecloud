<?php

namespace Tests\Feature\Backend\Api;

use Ds\Models\User;
use Tests\TestCase;

class UpdatesFeedControllerTest extends TestCase
{
    public function testInvokeUpdatesUserLastTimestamp()
    {
        $user = User::factory()->create();

        $this->assertNull($user->last_opened_updates_feed_at);

        $this->actingAsPassportUser($user)
            ->postJson(route('updates-feed'), [
                'last_opened_updates_feed_at' => now()->toIso8601String(),
            ])->assertNoContent();

        $user->refresh();

        $this->assertNotNull($user->last_opened_updates_feed_at);
    }
}
