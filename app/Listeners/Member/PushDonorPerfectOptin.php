<?php

namespace Ds\Listeners\Member;

use Ds\Enums\MemberOptinSource;
use Ds\Events\MemberOptinChanged;
use Ds\Services\DonorPerfectService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class PushDonorPerfectOptin implements ShouldQueue
{
    use Queueable;

    /** @var \Ds\Services\DonorPerfectService */
    private $donorPerfectService;

    public function __construct(DonorPerfectService $donorPerfectService)
    {
        $this->donorPerfectService = $donorPerfectService;
    }

    public function handle(MemberOptinChanged $event): void
    {
        $this->donorPerfectService->updateDonorFromAccount($event->member);
    }

    public function shouldQueue(MemberOptinChanged $event): bool
    {
        $optinChangedByDonorPerfect = $event->member->optinLogs->last()->source === MemberOptinSource::DONOR_PERFECT;

        return dpo_is_enabled()
            && $event->member->donor_id
            && ! $optinChangedByDonorPerfect;
    }

    public function viaQueue()
    {
        return 'low';
    }
}
