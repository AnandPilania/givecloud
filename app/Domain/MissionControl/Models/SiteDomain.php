<?php

namespace Ds\Domain\MissionControl\Models;

use Ds\Common\DataAccess;

class SiteDomain extends DataAccess
{
    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'site_id' => 'integer',
        'status_checked_at' => 'datetime',
        'ssl_enabled' => 'boolean',
        'using_cloudflare' => 'boolean',
        'using_rackspace' => 'boolean',
    ];
}
