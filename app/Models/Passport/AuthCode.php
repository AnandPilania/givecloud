<?php

namespace Ds\Models\Passport;

use Illuminate\Database\Eloquent\Builder;
use Laravel\Passport\AuthCode as PassportAuthCode;

class AuthCode extends PassportAuthCode
{
    use HasSiteScope;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'revoked' => 'bool',
        'site_id' => 'int',
        'user_id' => 'int',
    ];

    // We override registerSiteScope() as 'site_id' cannot be null in this model.
    protected static function registerSiteScope()
    {
        static::addGlobalScope('site', function (Builder $builder) {
            $builder->where('site_id', site()->id);
        });
    }
}
