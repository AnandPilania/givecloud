<?php

namespace Ds\Models;

use Ds\Illuminate\Database\Eloquent\Factories\HasFactory;
use Ds\Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FundraisingPageReport extends Model
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
        'reported_at',
    ];

    /**
     * The attributes that can be mass assigned.
     *
     * @var string[]
     */
    protected $fillable = [
        'reason',
        'reported_at',
    ];

    /**
     * Default attributes and values.
     *
     * @var array
     */
    protected $attributes = [];

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

    public function fundraisingPage(): BelongsTo
    {
        return $this->belongsTo(FundraisingPage::class);
    }
}
