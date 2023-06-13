<?php

namespace Ds\Models;

use Ds\Illuminate\Database\Eloquent\Factories\HasFactory;
use Ds\Illuminate\Database\Eloquent\Model;
use Ds\Models\Traits\HasUserAgent;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberOptinLog extends Model
{
    use HasFactory;
    use HasUserAgent;

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
