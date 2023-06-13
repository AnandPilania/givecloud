<?php

namespace Ds\Models;

use Ds\Eloquent\Userstamps;
use Ds\Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ShippingTier extends Model
{
    use Userstamps;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'shipping_tier';

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'min_value' => 'double',
        'max_value' => 'double',
        'pivot_amount' => 'double',
    ];

    public function shippingMethods(): BelongsToMany
    {
        return $this->belongsToMany(ShippingMethod::class, 'shipping_value', 'tier_id', 'method_id')
            ->withPivot('amount');
    }

    /**
     * Attribute mask: is_infinite
     * Is this tier have an infinite max value?
     *
     * @return bool
     */
    public function getIsInfiniteAttribute()
    {
        return $this->min_value > 0 && $this->max_value == 0;
    }
}
