<?php

namespace Ds\Events;

use Ds\Models\Member as Account;
use Illuminate\Queue\SerializesModels;

class AccountCreated extends Event implements AccountEventInterface
{
    use SerializesModels;

    /** @var \Ds\Models\Account */
    public $account;

    /**
     * Create a new event instance.
     *
     * @param \Ds\Models\Account $account
     * @return void
     */
    public function __construct(Account $account)
    {
        $this->account = $account;
    }
}
