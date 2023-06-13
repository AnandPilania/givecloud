<?php

namespace Ds\Events;

use Ds\Models\User;
use Illuminate\Queue\SerializesModels;

class UserCreated extends Event
{
    use SerializesModels;

    /** @var \Ds\Models\User */
    public $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }
}
