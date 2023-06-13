<?php

namespace Ds\Models\Passport;

use Laravel\Passport\Client as PassportClient;

class Client extends PassportClient
{
    use HasSiteScope;

    public const ZAPIER_CLIENT_NAME = 'zapier';
    public const ZAPIER_CLIENT_ID = 3;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'grant_types' => 'array',
        'personal_access_client' => 'bool',
        'password_client' => 'bool',
        'revoked' => 'bool',
        'site_id' => 'int',
        'user_id' => 'int',
    ];
}
