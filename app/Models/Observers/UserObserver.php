<?php

namespace Ds\Models\Observers;

use Ds\Events\UserCreated;
use Ds\Events\UserWasUpdated;
use Ds\Models\User;

class UserObserver
{
    public function created(User $user)
    {
        event(new UserCreated($user));
    }

    public function updated(User $user): void
    {
        event(new UserWasUpdated($user));
    }
}
