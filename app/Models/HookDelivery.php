<?php

namespace Ds\Models;

use Ds\Illuminate\Database\Eloquent\Factories\HasFactory;
use Ds\Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HookDelivery extends Model
{
    use HasFactory;

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
    protected $dates = [
        'delivered_at',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'completed_in' => 'float',
        'event' => 'string',
        'hook_id' => 'integer',
        'req_body' => 'string',
        'req_headers' => 'string',
        'res_status' => 'integer',
        'res_body' => 'string',
        'res_headers' => 'string',
    ];

    public function hook(): BelongsTo
    {
        return $this->belongsTo(Hook::class);
    }

    public function getPayloadJsonPrettyAttribute(): string
    {
        return json_encode(json_decode($this->req_body), JSON_PRETTY_PRINT);
    }
}
