<?php

namespace Ds\Models;

use Ds\Models\Traits\HasUserAgent;
use OwenIt\Auditing\Models\Audit as BaseAudit;

class Audit extends BaseAudit
{
    use HasUserAgent;
}
