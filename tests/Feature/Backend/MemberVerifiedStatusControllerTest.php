<?php

namespace Tests\Feature\Backend;

use Ds\Enums\Supporters\SupporterVerifiedStatus;
use Ds\Models\Member;
use Tests\TestCase;

class MemberVerifiedStatusControllerTest extends TestCase
{
    public function testCanVerifyMember(): void
    {
        $member = Member::factory()->unverified()->create();

        $this->assertNull($member->verified_status);

        $this->actingAsUser($this->createUserWithPermissions())
            ->get(route('backend.supporter_verification.verify', $member))
            ->assertRedirect();

        $member->refresh();

        $this->assertSame($member->verified_status, SupporterVerifiedStatus::VERIFIED);
    }

    public function testCanDenyMember(): void
    {
        $member = Member::factory()->verified()->create();

        $this->assertSame($member->verified_status, SupporterVerifiedStatus::VERIFIED);

        $this->actingAsUser($this->createUserWithPermissions())
            ->get(route('backend.supporter_verification.deny', $member))
            ->assertRedirect();

        $member->refresh();

        $this->assertSame($member->verified_status, SupporterVerifiedStatus::DENIED);
    }
}
