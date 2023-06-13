<?php

namespace Ds\Models;

use Ds\Eloquent\Permissions;
use Ds\Eloquent\Userstamps;
use Ds\Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Comment extends Model
{
    use HasFactory;
    use Permissions;
    use Userstamps;

    protected $fillable = [
        'body',
        'commentable_id',
        'commentable_type',
    ];

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }
}
