<?php

namespace Ds\Models;

use Ds\Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheckIn extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'ticket_check_in';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['check_in_at'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class)->withSpam();
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'check_in_by');
    }
}
