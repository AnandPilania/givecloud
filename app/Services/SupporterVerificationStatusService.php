<?php

namespace Ds\Services;

use Ds\Enums\Supporters\SupporterVerifiedStatus;
use Ds\Models\Member;
use Illuminate\Database\Query\JoinClause;

class SupporterVerificationStatusService
{
    public function supporterIsDenied(?Member $member = null): bool
    {
        if (! sys_get('bool:fundraising_pages_requires_verify')) {
            return false;
        }

        if (! $member) {
            return false;
        }

        return $member->isDenied;
    }

    public function supporterIsNotDenied(?Member $member = null): bool
    {
        return ! $this->supporterIsDenied($member);
    }

    public function supporterIsVerifiedOrIsItself(Member $member): bool
    {
        if (sys_get('fundraising_pages_requires_verify') !== '1') {
            return true;
        }

        if ($member->id === member('id')) {
            return true;
        }

        return $member->verified_status === SupporterVerifiedStatus::VERIFIED;
    }

    public function updateSupporterStatus(Member $member): bool
    {
        if (! sys_get('bool:fundraising_pages_requires_verify')) {
            return false;
        }

        if (is_null($member->verified_status)) {
            $member->verified_status = SupporterVerifiedStatus::PENDING;
        }

        $this->updateSupporterStatusBasedOnPastDonations($member);

        return $member->save();
    }

    public function updateSupporterStatusBasedOnPastDonations(Member $member): Member
    {
        if (! sys_get('bool:fundraising_pages_requires_verify')) {
            return $member;
        }

        if (! sys_get('bool:fundraising_pages_auto_verifies')) {
            return $member;
        }

        if ($member->orders()->count() > 0) {
            $member->verified_status = SupporterVerifiedStatus::VERIFIED;
        }

        return $member;
    }

    public function updateSupporterWithActivePages(): bool
    {
        return Member::query()
            ->rightJoin('fundraising_pages', function (JoinClause $join) {
                $join->on('member.id', '=', 'fundraising_pages.member_organizer_id')
                    ->where('fundraising_pages.status', 'active');
            })->update(['verified_status' => SupporterVerifiedStatus::VERIFIED]);
    }
}
