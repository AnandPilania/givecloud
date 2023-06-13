<?php

namespace Ds\Events;

use Ds\Models\Member;
use Illuminate\Queue\SerializesModels;

class AccountWasRegistered extends Event
{
    use SerializesModels;

    /** @var \Ds\Models\Member */
    public $member;

    /**
     * Create a new event instance.
     *
     * @param \Ds\Models\Member $member
     * @return void
     */
    public function __construct(Member $member)
    {
        $this->member = $member;
    }
}
