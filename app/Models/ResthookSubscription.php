<?php

namespace Ds\Models;

use Ds\Illuminate\Database\Eloquent\Factories\HasFactory;
use Ds\Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResthookSubscription extends Model
{
    use HasFactory;

    /**
     * The attributes that are not mass assignable.
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be casted to native types.
     */
    protected $casts = [
        'event' => 'string',
        'target_url' => 'string',
        'user_id' => 'int',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
