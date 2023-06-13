<?php

namespace Ds\Models;

use Ds\Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPageVisit extends Model
{
    protected $fillable = [
        'url',
        'user_id',
        'user_login_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function userLogin(): BelongsTo
    {
        return $this->belongsTo(UserLogin::class);
    }
}
