<?php

namespace Ds\Jobs;

use Ds\Models\Member;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CalculateLifetimeMemberGiving implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use SerializesModels;
    use Queueable;

    /** @var \Ds\Models\Member */
    protected $member;

    /**
     * @param \Ds\Models\Member $member
     */
    public function __construct(Member $member)
    {
        $this->member = $member;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->member->saveLifeTimeTotals();
    }
}
