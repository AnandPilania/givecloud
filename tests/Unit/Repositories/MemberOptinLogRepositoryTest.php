<?php

namespace Tests\Unit\Repositories;

use Ds\Models\Member;
use Ds\Models\MemberOptinLog;
use Ds\Repositories\MemberOptinLogRepository;
use Tests\TestCase;

class MemberOptinLogRepositoryTest extends TestCase
{
    public function testGetLastLogFromMemberWithLogs(): void
    {
        $member = Member::factory()->create();
        $member->optinLogs()->saveMany(MemberOptinLog::factory(2)->make());
        $optin = MemberOptinLog::factory()->make();
        $member->optinLogs()->save($optin);

        /** @var \Ds\Repositories\MemberOptinLogRepository */
        $memberOptinLogRepository = $this->app->make(MemberOptinLogRepository::class);
        $optinLog = $memberOptinLogRepository->getLastLogFromMember($member->getKey());

        $this->assertInstanceOf(MemberOptinLog::class, $optinLog);
        $this->assertEquals($optin->toArray(), $optinLog->toArray());
    }

    public function testGetLastLogFromMemberWithoutLogs(): void
    {
        $this->assertNull(
            $this->app->make(MemberOptinLogRepository::class)
                ->getLastLogFromMember(Member::factory()->create()->getKey())
        );
    }
}
