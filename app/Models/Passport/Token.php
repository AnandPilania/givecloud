<?php

namespace Ds\Models\Passport;

use Ds\Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Passport\Token as PassportToken;

class Token extends PassportToken
{
    use HasFactory;
    use HasSiteScope;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'scopes' => 'array',
        'revoked' => 'bool',
        'site_id' => 'int',
        'user_id' => 'int',
    ];
}
