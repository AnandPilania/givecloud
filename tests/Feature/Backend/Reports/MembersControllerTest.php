<?php

namespace Tests\Feature\Backend\Reports;

use Ds\Models\Member;
use Ds\Models\Membership;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class MembersControllerTest extends TestCase
{
    public function testReturnsAllMemberships(): void
    {
        $this->createAllMemberships();

        $this
            ->actingAsUser($this->createUserWithPermissions(['member.', 'reports.']))
            ->post(route('backend.reports.members.ajax'))
            ->assertOk()
            ->assertJson(function (AssertableJson $json) {
                $json->has('data', 11, function ($json) {
                    $json->where(6, 'Active')->etc();
                })->etc();
            });
    }

    /**
     * @dataProvider doesNotreturnDeletedDataProvider
     */
    public function testDoesNotReturnDeletedGroups($shouldDeleteGroup, $expectedCount): void
    {
        $user = $this->createUserWithPermissions(['member.', 'reports.']);
        $group = Membership::factory()->create();

        Member::factory()->hasAttached(
            $group,
            ['start_date' => toUtc('-1year'), 'end_date' => null],
            'groupAccountTimespans'
        )->create();

        if ($shouldDeleteGroup) {
            $group->delete();
        }

        $this
            ->actingAsUser($user)
            ->post(route('backend.reports.members.ajax'))
            ->assertOk()
            ->assertJson(function (AssertableJson $json) use ($expectedCount) {
                $json->has('data', $expectedCount)->etc();
            });
    }

    public function doesNotreturnDeletedDataProvider(): array
    {
        return [
            [true, 0], // $shouldDeleteGroup, $expectedCount
            [false, 1], // $shouldDeleteGroup, $expectedCount
        ];
    }

    public function testCanSearchBySupporter(): void
    {
        $this->createAllMemberships();

        Member::factory()->state([
            'first_name' => 'Shaggy Mister',
            'last_name' => 'Lover Lover',
        ])->hasAttached(
            Membership::factory(),
            ['start_date' => toUtc('-6months')],
            'groupAccountTimespans'
        )->create([
            'display_name' => 'Shaggy: Mister Lover Lover',
        ]);

        $this
            ->actingAsUser($this->createUserWithPermissions(['member.', 'reports.']))
            ->post(route('backend.reports.members.ajax'), ['search' => 'mister lover'])
            ->assertOk()
            ->assertJson(function (AssertableJson $json) {
                $json->has('data', 1, function ($json) {
                    $json->where(1, 'Shaggy Mister Lover Lover')->etc();
                })->etc();
            });
    }

    /**
     * @dataProvider statusFilterDataProvider
     */
    public function testCanFilterOnMembershipStatuses(string $status, string $message, int $expected): void
    {
        $this->createAllMemberships();

        $this
            ->actingAsUser($this->createUserWithPermissions(['member.', 'reports.']))
            ->post(route('backend.reports.members.ajax'), [
                'status' => $status,
            ])->assertOk()
            ->assertJson(function (AssertableJson $json) use ($expected, $message) {
                $json->has('data', $expected, function ($json) use ($message) {
                    $json->where(6, $message)->etc();
                })->etc();
            });
    }

    public function statusFilterDataProvider(): array
    {
        return [
            ['active', 'Active', 9],
            ['expiring', 'Active', 1],
            ['expired', 'Expired', 2],
            ['recently_expired', 'Expired', 1],
        ];
    }

    public function testCanFilterOnGroupId(): void
    {
        $data = $this->createActiveMemberships();
        $membership = $data->first()->groupAccountTimespans()->first();

        $this
            ->actingAsUser($this->createUserWithPermissions(['member.', 'reports.']))
            ->post(route('backend.reports.members.ajax'), [
                'group' => $membership->id,
            ])->assertOk()
            ->assertJson(function (AssertableJson $json) use ($membership) {
                $json->has('data', 1, function ($json) use ($membership) {
                    $json->where(3, $membership->name)->etc();
                })->etc();
            });
    }

    /**
     * @dataProvider startDatesDataProvider
     */
    public function testCanFilterOnStartDates(?string $startBefore, ?string $startAfter, int $expected): void
    {
        $this->createActiveMemberships(); // 8

        Member::factory()->hasAttached(
            Membership::factory(),
            ['start_date' => toUtc('-6months')],
            'groupAccountTimespans'
        )->create();

        $this
            ->actingAsUser($this->createUserWithPermissions(['member.', 'reports.']))
            ->post(route('backend.reports.members.ajax'), [
                'startDateBefore' => $startBefore ? toUtc($startBefore)->toDateString() : null,
                'startDateAfter' => $startAfter ? toUtc($startAfter)->toDateString() : null,
            ])->assertOk()
            ->assertJsonCount($expected, 'data');
    }

    public function startDatesDataProvider(): array
    {
        return [[
            'startDateBefore' => '-7months',
            'startDateAfter' => null,
            'expected' => 8,
        ], [
            'startDateBefore' => null,
            'startDateAfter' => '-7months',
            'expected' => 1,
        ], [
            'startDateBefore' => '-5months',
            'startDateAfter' => '-7months',
            'expected' => 1,
        ]];
    }

    /**
     * @dataProvider endDatesDataProvider
     */
    public function testCanFilterOnEndDates(?string $endBefore, ?string $endAfter, int $expected): void
    {
        $this->createActiveMemberships(); // 8

        Member::factory()->hasAttached(
            Membership::factory(),
            ['end_date' => toUtc('-6months')],
            'groupAccountTimespans'
        )->create();

        $this
            ->actingAsUser($this->createUserWithPermissions(['member.', 'reports.']))
            ->post(route('backend.reports.members.ajax'), [
                'endDateBefore' => $endBefore ? toUtc($endBefore)->toDateString() : null,
                'endDateAfter' => $endAfter ? toUtc($endAfter)->toDateString() : null,
            ])->assertOk()
            ->assertJsonCount($expected, 'data');
    }

    public function endDatesDataProvider(): array
    {
        return [[
            'endDateBefore' => '-5months',
            'endDateAfter' => null,
            'expected' => 1,
        ], [
            'endDateBefore' => null,
            'endDateAfter' => 'today',
            'expected' => 8,
        ], [
            'endDateBefore' => '-5months',
            'endDateAfter' => '-7months',
            'expected' => 1,
        ]];
    }

    protected function createAllMemberships(): void
    {
        $this->createActiveMemberships(); // 8 + 1 (expiring)
        $this->createExpiringMembership(); // 1
        $this->createExpiredMembership(); // 1 + 1 ( expiredLately)
        $this->createExpiredLatelyMembership(); // 1
    }

    protected function createActiveMemberships()
    {
        return
            Member::factory(4)->hasAttached(
                Membership::factory(2),
                ['start_date' => toUtc('-1year'), 'end_date' => null],
                'groupAccountTimespans'
            )->create();
    }

    protected function createExpiredLatelyMembership(): void
    {
        Member::factory()->hasAttached(
            Membership::factory(),
            ['start_date' => toUtc('-1year'), 'end_date' => toUtc('yesterday')],
            'groupAccountTimespans'
        )->create();
    }

    protected function createExpiredMembership(): void
    {
        Member::factory()->hasAttached(
            Membership::factory(),
            ['start_date' => toUtc('-14months'), 'end_date' => toUtc('-2months')],
            'groupAccountTimespans'
        )->create();
    }

    protected function createExpiringMembership(): void
    {
        Member::factory()->hasAttached(
            Membership::factory(),
            ['start_date' => toUtc('-6months'), 'end_date' => toUtc('+15days')],
            'groupAccountTimespans'
        )->create();
    }
}
