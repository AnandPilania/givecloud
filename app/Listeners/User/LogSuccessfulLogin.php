<?php

namespace Ds\Listeners\User;

use Ds\Models\User;
use Illuminate\Auth\Events\Login;

class LogSuccessfulLogin
{
    public function handle(Login $event): void
    {
        if (! is_a($event->user, User::class)) {
            return;
        }

        $event->user->trackLogin(auth()->viaRemember());
    }
}
