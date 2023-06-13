<?php

namespace Tests\Unit\Services;

use Ds\Enums\Supporters\SupporterVerifiedStatus;
use Ds\Models\FundraisingPage;
use Ds\Models\Member;
use Ds\Models\Order;
use Ds\Services\SupporterVerificationStatusService;
use Tests\TestCase;

class SupporterVerificationStatusServiceTest extends TestCase
{
    public function testMemberIsVerifiedWhenFeatureIsNotEnabled()
    {
        $member = Member::factory()->create();

        $this->assertTrue($this->app->make(SupporterVerificationStatusService::class)->supporterIsVerifiedOrIsItself($member));
    }

    public function testMemberIsNotVerifiedWhenFeatureIsEnabled()
    {
        sys_set('fundraising_pages_requires_verify', '1');

        $member = Member::factory()->create();

        $this->assertFalse($this->app->make(SupporterVerificationStatusService::class)->supporterIsVerifiedOrIsItself($member));
    }

    public function testMemberIsNotVerifiedWhenFeatureIsEnabledAndIsItself()
    {
        sys_set('fundraising_pages_requires_verify', '1');

        $member = Member::factory()->create();

        member_login_with_id($member->id);

        $this->assertTrue($this->app->make(SupporterVerificationStatusService::class)->supporterIsVerifiedOrIsItself($member));
    }

    public function testDoesNotSetMemberStatusIfFeatureNotEnabled()
    {
        $member = Member::factory()->create();

        $this->assertFalse($this->app->make(SupporterVerificationStatusService::class)->updateSupporterStatus($member));
    }

    public function testSetsMemberToPendingWhenFeatureEnabled()
    {
        sys_set('fundraising_pages_requires_verify', '1');

        $member = Member::factory()->create();

        $this->assertTrue($this->app->make(SupporterVerificationStatusService::class)->updateSupporterStatus($member));

        $member->refresh();

        $this->assertSame($member->verified_status, SupporterVerifiedStatus::PENDING);
    }

    public function testDoesNotSetMemberToVerifiedWhenAutoVerifyIsEnabledAndMemberHasNoOrders()
    {
        sys_set('fundraising_pages_requires_verify', '1');
        sys_set('fundraising_pages_auto_verifies', '1');

        $member = Member::factory()->create();

        $this->assertTrue($this->app->make(SupporterVerificationStatusService::class)->updateSupporterStatus($member));

        $member->refresh();

        $this->assertSame($member->verified_status, SupporterVerifiedStatus::PENDING);
    }

    public function testSetsMemberToVerifiedWhenAutoVerifyIsEnabledAndMemberHasOrders()
    {
        sys_set('fundraising_pages_requires_verify', '1');
        sys_set('fundraising_pages_auto_verifies', '1');

        $member = Member::factory()->has(Order::factory()->paid())->create();

        $this->assertTrue($this->app->make(SupporterVerificationStatusService::class)->updateSupporterStatus($member));

        $member->refresh();

        $this->assertSame($member->verified_status, SupporterVerifiedStatus::VERIFIED);
    }

    public function testCanVerifyFormerMembersWithActivePages()
    {
        sys_set('fundraising_pages_requires_verify', '1');
        sys_set('fundraising_pages_auto_verifies', '1');

        $memberWithActivePage = Member::factory()->has(FundraisingPage::factory()->active())->create();
        $memberWithClosedPage = Member::factory()->has(FundraisingPage::factory()->closed())->create();

        $this->app->make(SupporterVerificationStatusService::class)->updateSupporterWithActivePages();

        $memberWithActivePage->refresh();
        $memberWithClosedPage->refresh();

        $this->assertSame($memberWithActivePage->verified_status, SupporterVerifiedStatus::VERIFIED);
        $this->assertSame($memberWithClosedPage->verified_status, SupporterVerifiedStatus::PENDING);
    }
}
