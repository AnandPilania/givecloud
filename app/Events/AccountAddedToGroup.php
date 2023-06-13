<?php

namespace Ds\Events;

use Ds\Models\GroupAccount;
use Illuminate\Queue\SerializesModels;

class AccountAddedToGroup extends Event
{
    use SerializesModels;

    /** @var \Ds\Models\GroupAccount */
    public $group_account;

    /**
     * Create a new event instance.
     *
     * @param \Ds\Models\GroupAccount $group_account
     * @return void
     */
    public function __construct(GroupAccount $group_account)
    {
        $this->group_account = $group_account;
    }
}
