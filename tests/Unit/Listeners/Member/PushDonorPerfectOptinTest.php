<?php

namespace Tests\Unit\Listeners\Member;

use Ds\Events\MemberOptinChanged;
use Ds\Listeners\Member\PushDonorPerfectOptin;
use Ds\Models\Member;
use Ds\Models\MemberOptinLog;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class PushDonorPerfectOptinTest extends TestCase
{
    public function testEventListenerIsListeningOnMemberOptinChanged(): void
    {
        Event::fake();

        Event::assertListening(MemberOptinChanged::class, PushDonorPerfectOptin::class);
    }

    public function testDoNotPushToDPWhenMissingDonorID(): void
    {
        sys_set('dpo_api_key', 'somerandomapikey');
        $member = Member::factory()->create(['donor_id' => null]);
        MemberOptinLog::factory()->for($member)->optin()->create();

        $this->assertFalse(
            $this->app->make(PushDonorPerfectOptin::class)->shouldQueue((new MemberOptinChanged($member)))
        );
    }
}
