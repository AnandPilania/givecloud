<?php

namespace Ds\Events;

use Ds\Models\Member as Account;
use Illuminate\Queue\SerializesModels;

class AccountWasUpdated extends Event implements AccountEventInterface
{
    use SerializesModels;

    /** @var \Ds\Models\Member */
    public $account;

    public function __construct(Account $account)
    {
        $this->account = $account;
    }
}
