<?php

namespace Tests\Unit\Models\Observers;

use Ds\Models\GroupAccount;
use Ds\Models\GroupAccountTimespan;
use Ds\Models\Membership as Group;
use Tests\TestCase;

class GroupAccountObserverTest extends TestCase
{
    public function testTimespanGeneratedOnCreatedEvent()
    {
        $groupAccount = GroupAccount::factory()->create();

        $this->assertNotNull($groupAccount->groupAccountTimespan);
    }

    public function testTimespanRegeneratedOnCreatedEvent()
    {
        $groupAccount = GroupAccount::factory()->create([
            'start_date' => now(),
            'end_date' => now()->addYear(),
        ]);

        $originalTimespanId = $groupAccount->groupAccountTimespan->getKey();

        GroupAccount::factory()->create([
            'account_id' => $groupAccount->account_id,
            'group_id' => $groupAccount->group_id,
            'start_date' => $groupAccount->start_date,
            'end_date' => now()->addYears(2),
        ]);

        $this->assertNull(GroupAccountTimespan::find($originalTimespanId));

        $this->assertNotSame(
            $originalTimespanId,
            $groupAccount->refresh()->groupAccountTimespan->getKey()
        );
    }

    public function testOrphanedTimespanCleanupOnUpdatedEvent()
    {
        $group = Group::factory()->create();

        $groupAccount = GroupAccount::factory()->create([
            'start_date' => now(),
            'end_date' => now()->addYear(),
        ]);

        $originalTimespanId = $groupAccount->groupAccountTimespan->getKey();
        $groupAccount->group_id = $group->id;
        $groupAccount->update();

        $this->assertNull(GroupAccountTimespan::find($originalTimespanId));

        $this->assertNotSame(
            $originalTimespanId,
            $groupAccount->refresh()->groupAccountTimespan->getKey()
        );
    }

    public function testTimespanDeletedOnDeletedEvent()
    {
        $groupAccount = GroupAccount::factory()->create();
        $timespanId = $groupAccount->groupAccountTimespan->getKey();

        $groupAccount->delete();

        $this->assertNull(GroupAccountTimespan::find($timespanId));
    }
}
