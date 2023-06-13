<?php

namespace Ds\Facades;

use Ds\Services\SiteService;
use Illuminate\Support\Facades\Facade;

/**
 * @see \Ds\Services\Site
 */
class Site extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return SiteService::class;
    }
}
