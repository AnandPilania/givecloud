<?php

namespace Ds\Models\Passport;

use Laravel\Passport\HasApiTokens as PassportHasApiTokens;
use Laravel\Passport\Passport;

trait HasApiTokens
{
    /*
     * Extending Laravel Passport HasApiToken.
     * This overrides clients() and tokens() methods by renaming them.
     */
    use PassportHasApiTokens {
        PassportHasApiTokens::clients as passportHasApiTokensClients;
        PassportHasApiTokens::tokens as passportHasApiTokensToken;
    }

    /**
     * Get all of the user's registered OAuth clients.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function clients()
    {
        return $this
            ->hasMany(Passport::clientModel(), 'user_id')
            ->where('site_id', site()->id);
    }

    /**
     * Get all of the access tokens for the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tokens()
    {
        return $this
            ->hasMany(Passport::tokenModel(), 'user_id')
            ->where('site_id', site()->id)
            ->orderBy('created_at', 'desc');
    }
}
