<?php

namespace Ds\Illuminate\Database\Eloquent;

use OwenIt\Auditing\Resolvers\UserResolver as LaravelUserResolver;

class UserResolver extends LaravelUserResolver
{
    /**
     * @return mixed
     */
    public static function resolve()
    {
        return parent::resolve() ?? member();
    }
}
