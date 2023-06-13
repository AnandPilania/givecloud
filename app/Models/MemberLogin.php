<?php

namespace Ds\Models;

use Ds\Illuminate\Database\Eloquent\Factories\HasFactory;
use Ds\Illuminate\Database\Eloquent\Model;
use Ds\Models\Traits\HasUserAgent;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberLogin extends Model
{
    use HasFactory;
    use HasUserAgent;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'member_login';

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
    protected $dates = ['login_at'];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
