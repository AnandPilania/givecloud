<?php

namespace Tests\Unit\Eloquent;

use Ds\Models\User;
use Tests\StoryBuilder;
use Tests\TestCase;

class SoftDeleteUserstampTest extends TestCase
{
    public function testUserstampSetWhenModelDeleted(): void
    {
        $user = User::factory()->create();
        $this->actingAsUser($user);

        $contribution = StoryBuilder::onetimeContribution()->create();
        $contribution->delete();

        $this->assertSame($user->id, $contribution->refresh()->deleted_by);
    }
}
