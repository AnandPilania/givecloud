<?php

namespace Ds\Models;

use Ds\Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Region extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'region';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    public function taxes(): BelongsToMany
    {
        return $this->belongsToMany(Tax::class, 'producttaxregion', 'regionid', 'taxid');
    }
}
