<?php

namespace Ds\Models;

use Ds\Illuminate\Database\Eloquent\Relations\Pivot;

class PaymentPivot extends Pivot
{
    /** @var string */
    protected $table = 'payments_pivot';
}
