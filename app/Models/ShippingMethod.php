<?php

namespace Ds\Models;

use Ds\Eloquent\SoftDeleteUserstamp;
use Ds\Eloquent\Userstamps;
use Ds\Illuminate\Database\Eloquent\Factories\HasFactory;
use Ds\Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShippingMethod extends Model
{
    use HasFactory;
    use SoftDeletes;
    use SoftDeleteUserstamp;
    use Userstamps;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'shipping_method';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'priority' => 'integer',
        'show_on_web' => 'boolean',
        'is_default' => 'boolean',
        'countries' => 'array',
        'regions' => 'array',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function shippingTiers(): BelongsToMany
    {
        return $this->belongsToMany(ShippingTier::class, 'shipping_value', 'method_id', 'tier_id')
            ->withPivot('amount');
    }

    /**
     * Returns a collection of available shipping methods by region.
     *
     * @param string $region a name/code for a region/province/state
     * @param string $country an ISO country code
     * @return \Illuminate\Support\Collection a collection of all available methods in the given region
     */
    public static function getByGeography($region = null, $country = null)
    {
        $region = trim($region);
        $country = trim($country);

        $methods = self::select()
            ->orderBy('is_default', 'desc')
            ->orderBy('priority')
            ->orderBy('name', 'asc')
            ->get();

        return $methods
            ->filter(function ($method) use ($region) {
                return $method->regions === null || in_array($region, $method->regions);
            })->filter(function ($method) use ($country) {
                return $method->countries === null || in_array($country, $method->countries);
            });
    }

    /**
     * Determine the cost of shipping based on a method and a subtotal
     *
     * ##### TO DO #####
     * - fix this so it throws proper errors (calls to cart_get() make
     *   this difficult for the donor-facing side)
     *
     * @return float
     */
    public static function getShippingCost($shipping_method_id, $subtotal)
    {
        // if we DON'T find a method
        if (! is_numeric($shipping_method_id)) {
            return 0.0;
        }

        // find the shipping method (its not guaranteed it exists)
        $shipping_method = self::find($shipping_method_id);

        // if we DON'T find a method
        if (! $shipping_method) {
            return 0.0;
        }

        // find the tier
        $tier = $shipping_method->shippingTiers()
            ->where('min_value', '<=', $subtotal)
            ->where('max_value', '>=', $subtotal)
            ->first();

        // if we DON'T find a tier
        if (! $tier) {
            return 0.0;
        }

        // return the amount
        return (float) $tier->pivot->amount;
        // return the cost
    }
}
