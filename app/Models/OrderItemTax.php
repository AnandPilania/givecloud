<?php

namespace Ds\Models;

use Ds\Illuminate\Database\Eloquent\Model;

class OrderItemTax extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'productorderitemtax';

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'orderitemid' => 'integer',
        'taxid' => 'integer',
        'amount' => 'double',
    ];
}
