<?php

namespace Tests\Unit\Services;

use Ds\Enums\MemberOptinAction;
use Ds\Events\MemberOptinChanged;
use Ds\Models\Member;
use Ds\Services\MemberService;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * @group backend
 * @group services
 * @group member
 */
class MemberServiceTest extends TestCase
{
    public function testUpdateOptinDoesNothingWhenNull(): void
    {
        /** @var \Ds\Models\Member */
        $member = Member::factory()->create();

        /** @var \Ds\Services\MemberService */
        $memberService = $this->app->make(MemberService::class, compact('member'));
        $member = $memberService->updateOptin(null);

        $this->assertEmpty($member->optinLogs);
    }

    public function testUpdateOptinAddsOptinLog(): void
    {
        Event::fake();
        /** @var \Ds\Models\Member */
        $member = Member::factory()->create();

        /** @var \Ds\Services\MemberService */
        $memberService = $this->app->make(MemberService::class, compact('member'));
        $member = $memberService->updateOptin(true, 'this reason should be ignored as it is not an opt out');

        $this->assertCount(1, $member->optinLogs);
        $optinLog = $member->optinLogs->first();
        $this->assertSame(MemberOptinAction::OPTIN, $optinLog->action);
        $this->assertNull($optinLog->reason);
        Event::assertDispatched(MemberOptinChanged::class);
    }
}
