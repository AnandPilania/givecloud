<?php

namespace Tests\Unit\Listeners\User;

use Ds\Domain\MissionControl\Listeners\UpdateMissionControlSiteUsers;
use Ds\Events\UserCreated;
use Ds\Events\UserWasUpdated;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class UpdateMissionControlSiteUsersTest extends TestCase
{
    public function testIsListeningOnUserCreated(): void
    {
        Event::fake();

        Event::assertListening(UserCreated::class, UpdateMissionControlSiteUsers::class);
    }

    public function testIsListeningOnUserUpdated(): void
    {
        Event::fake();

        Event::assertListening(UserWasUpdated::class, UpdateMissionControlSiteUsers::class);
    }
}
