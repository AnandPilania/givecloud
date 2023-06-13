<?php

namespace Ds\Services;

use Ds\Enums\MemberOptinAction;
use Ds\Enums\MemberOptinSource;
use Ds\Events\MemberOptinChanged;
use Ds\Models\Member;
use Ds\Models\MemberOptinLog;
use Ds\Repositories\MemberOptinLogRepository;

class MemberService
{
    /** @var \Ds\Models\Member */
    private $member;

    /** @var \Ds\Repositories\MemberOptinLogRepository */
    private $memberOptinLogRepository;

    public function __construct(
        Member $member,
        MemberOptinLogRepository $memberOptinLogRepository
    ) {
        $this->member = $member;
        $this->memberOptinLogRepository = $memberOptinLogRepository;
    }

    public function optin(?string $source = null, ?string $ipAddress = null, ?string $userAgent = null): Member
    {
        return $this->updateOptin(true, null, $source, $ipAddress, $userAgent);
    }

    public function optout(
        ?string $reason = null,
        ?string $source = null,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): Member {
        return $this->updateOptin(false, $reason, $source, $ipAddress, $userAgent);
    }

    public function updateOptin(
        ?bool $isOptin = null,
        ?string $optOutReason = null,
        ?string $source = MemberOptinSource::WEBSITE,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): Member {
        if (null === $isOptin) {
            return $this->member;
        }

        $optinAction = $isOptin ? MemberOptinAction::OPTIN : MemberOptinAction::OPTOUT;
        $memberLastOptin = $this->memberOptinLogRepository->getLastLogFromMember($this->member->getKey());

        $optinHasNotChanged = $memberLastOptin && $memberLastOptin->action === $optinAction;
        if ($optinHasNotChanged) {
            return $this->member;
        }

        $newMemberOptinLog = (new MemberOptinLog)->fill([
            'action' => $optinAction,
            'ip' => $ipAddress ?: request()->ip(),
            'reason' => $isOptin ? null : $optOutReason,
            'source' => $source,
            'user_agent' => $userAgent ?: request()->userAgent(),
        ]);

        $this->member->optinLogs()->save($newMemberOptinLog);

        event(new MemberOptinChanged($this->member));

        return $this->member;
    }

    public function setMember(Member $member): self
    {
        $this->member = $member;

        return $this;
    }
}
