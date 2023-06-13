<?php

namespace Ds\Illuminate\Auth;

use Ds\Models\User;
use Illuminate\Auth\EloquentUserProvider as BaseEloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;
use Illuminate\Support\Facades\Hash;

class EloquentUserProvider extends BaseEloquentUserProvider
{
    /**
     * Validate a user against the given credentials.
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     * @param array $credentials
     * @return bool
     */
    public function validateCredentials(UserContract $user, array $credentials)
    {
        // moving forward the super/support user MUST authenticate
        // via MissionControl. attempts to validate credentials for
        // super/support user should ALWAYS fail except in local dev
        if ($user instanceof User && is_super_user($user) && ! isDev()) {
            return false;
        }

        // check for passwords stored in the clear
        // and update to use hashed password storage
        if ($user instanceof User && $user->password !== null) {
            $user->hashed_password = Hash::make($user->password);
            $user->password = null;
            $user->save();
        }

        return parent::validateCredentials($user, $credentials);
    }
}
