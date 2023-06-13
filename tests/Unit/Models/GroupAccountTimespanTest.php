<?php

namespace Tests\Unit\Models;

use Ds\Models\GroupAccount;
use Ds\Models\GroupAccountTimespan;
use Ds\Models\Member;
use Ds\Models\Membership;
use Tests\TestCase;

class GroupAccountTimespanTest extends TestCase
{
    public function testReturnsSingleAggregate(): void
    {
        $membership = Membership::factory()->create();
        $account = Member::factory()->create();

        $groupAccount =
            GroupAccount::factory()->create([
                'group_id' => $membership,
                'account_id' => $account,
                'start_date' => fromLocal('-1year'),
                'end_date' => fromLocal('now'),
            ]);

        $aggregates = GroupAccountTimespan::aggregate($membership->id, $account->id);

        $this->assertCount(1, $aggregates);
        $this->assertSame($groupAccount->start_date->toDateString(), $aggregates->first()->start_date->toDateString());
        $this->assertSame($groupAccount->end_date->toDateString(), $aggregates->first()->end_date->toDateString());
    }

    public function testReturnsAggregatedDates(): void
    {
        $membership = Membership::factory()->create();
        $account = Member::factory()->create();

        $past = GroupAccount::factory()->create([
            'group_id' => $membership,
            'account_id' => $account,
            'start_date' => fromLocal('-1year'),
            'end_date' => fromLocal('now'),
        ]);

        $present = GroupAccount::factory()->create([
            'group_id' => $membership,
            'account_id' => $account,
            'start_date' => fromLocal('now'),
            'end_date' => fromLocal('+1year'),
        ]);

        $aggregated = GroupAccountTimespan::aggregate($membership->id, $account->id)->first();

        $this->assertSame($past->start_date->toDateString(), $aggregated->start_date->toDateString());
        $this->assertSame($present->end_date->toDateString(), $aggregated->end_date->toDateString());
    }

    public function testReturnsAggregatedDatesForOverlap(): void
    {
        $membership = Membership::factory()->create();
        $account = Member::factory()->create();

        $past = GroupAccount::factory()->create([
            'group_id' => $membership,
            'account_id' => $account,
            'start_date' => fromLocal('-1year'),
            'end_date' => fromLocal('now'),
        ]);

        $present = GroupAccount::factory()->create([
            'group_id' => $membership,
            'account_id' => $account,
            'start_date' => fromLocal('-6months'),
            'end_date' => fromLocal('+6months'),
        ]);

        $aggregated = GroupAccountTimespan::aggregate($membership->id, $account->id)->first();

        $this->assertSame($past->start_date->toDateString(), $aggregated->start_date->toDateString());
        $this->assertSame($present->end_date->toDateString(), $aggregated->end_date->toDateString());
    }

    public function testAggregatesNullableStartDate(): void
    {
        $membership = Membership::factory()->create();
        $account = Member::factory()->create();

        $past = GroupAccount::factory()->create([
            'group_id' => $membership,
            'account_id' => $account,
            'start_date' => fromLocal('-1year'),
            'end_date' => fromLocal('now'),
        ]);

        GroupAccount::factory()->create([
            'group_id' => $membership,
            'account_id' => $account,
            'start_date' => null,
            'end_date' => fromLocal('-6months'),
        ]);

        $aggregated = GroupAccountTimespan::aggregate($membership->id, $account->id)->first();

        $this->assertSame($past->end_date->toDateString(), $aggregated->end_date->toDateString());
        $this->assertNull($aggregated->start_date);
    }

    public function testAggregatesNullableEndDate(): void
    {
        $membership = Membership::factory()->create();
        $account = Member::factory()->create();

        $past = GroupAccount::factory()->create([
            'group_id' => $membership,
            'account_id' => $account,
            'start_date' => fromLocal('-1year'),
            'end_date' => fromLocal('now'),
        ]);

        GroupAccount::factory()->create([
            'group_id' => $membership,
            'account_id' => $account,
            'start_date' => fromLocal('-6months'),
            'end_date' => null,
        ]);

        $aggregated = GroupAccountTimespan::aggregate($membership->id, $account->id)->first();

        $this->assertSame($past->start_date->toDateString(), $aggregated->start_date->toDateString());
        $this->assertNull($aggregated->end_date);
    }

    public function testReturnsMultipleAggregatesWhenNotDistinctPeriods(): void
    {
        $membership = Membership::factory()->create();
        $account = Member::factory()->create();

        $past = GroupAccount::factory()->create([
            'group_id' => $membership,
            'account_id' => $account,
            'start_date' => fromLocal('-1year'),
            'end_date' => fromLocal('today'),
        ]);

        $future = GroupAccount::factory()->create([
            'group_id' => $membership,
            'account_id' => $account,
            'start_date' => fromLocal('+18days'),
            'end_date' => fromLocal('+1year'),
        ]);

        $aggregates = GroupAccountTimespan::aggregate($membership->id, $account->id);
        $pastAggregate = $aggregates->first();
        $futureAggregate = $aggregates->slice(1, 1)->first();

        $this->assertSame(2, $aggregates->count());
        $this->assertSame($past->start_date->toDateString(), $pastAggregate->start_date->toDateString());
        $this->assertSame($past->end_date->toDateString(), $pastAggregate->end_date->toDateString());
        $this->assertSame($future->start_date->toDateString(), $futureAggregate->start_date->toDateString());
        $this->assertSame($future->end_date->toDateString(), $futureAggregate->end_date->toDateString());
    }
}
