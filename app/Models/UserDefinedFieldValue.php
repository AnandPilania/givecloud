<?php

namespace Ds\Models;

use Ds\Models\Observers\UserDefinedFieldValueObserver;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphPivot;

class UserDefinedFieldValue extends MorphPivot
{
    public $incrementing = true;

    public $timestamps = true;

    protected $casts = [
        'value' => 'json',
    ];

    protected $table = 'user_defined_fieldables';

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        self::observe(new UserDefinedFieldValueObserver);
    }

    public function userDefinedField(): BelongsTo
    {
        return $this->belongsTo(UserDefinedField::class);
    }
}
