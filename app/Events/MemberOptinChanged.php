<?php

namespace Ds\Events;

use Ds\Models\Member;
use Illuminate\Queue\SerializesModels;

class MemberOptinChanged extends Event
{
    use SerializesModels;

    /** @var \Ds\Models\Member */
    public $member;

    public function __construct(Member $member)
    {
        $this->member = $member;
    }
}
