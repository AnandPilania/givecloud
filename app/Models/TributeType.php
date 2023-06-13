<?php

namespace Ds\Models;

use Ds\Eloquent\Permissions;
use Ds\Eloquent\SoftDeleteUserstamp;
use Ds\Eloquent\Userstamps;
use Ds\Illuminate\Database\Eloquent\Factories\HasFactory;
use Ds\Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TributeType extends Model
{
    use HasFactory;
    use Permissions;
    use SoftDeleteUserstamp;
    use SoftDeletes;
    use Userstamps;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'deleted_at',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_enabled' => 'boolean',
    ];

    /**
     * Attributes hidden from serialization.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [];

    /**
     * Scope: Active tribute types
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeActive($query)
    {
        return $query->where('is_enabled', 1)
            ->orderBy('sequence');
    }
}
