<?php

namespace Ds\Facades;

use Ds\Services\ConfigService;
use Illuminate\Support\Facades\Facade;

class SysConfig extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ConfigService::class;
    }
}
