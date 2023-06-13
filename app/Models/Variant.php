<?php

namespace Ds\Models;

use Ds\Domain\Theming\Liquid\Liquidable;
use Ds\Eloquent\Hashids;
use Ds\Eloquent\HasMetadata;
use Ds\Eloquent\Metadatable;
use Ds\Eloquent\SoftDeleteBooleans;
use Ds\Illuminate\Database\Eloquent\Auditable;
use Ds\Illuminate\Database\Eloquent\Factories\HasFactory;
use Ds\Illuminate\Database\Eloquent\HasAuditing;
use Ds\Illuminate\Database\Eloquent\Model;
use Ds\Models\Traits\HasEmails;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Variant extends Model implements Auditable, Liquidable, Metadatable
{
    use HasAuditing;
    use HasEmails;
    use HasFactory;
    use Hashids;
    use HasMetadata;
    use SoftDeleteBooleans;

    /**
     * The name of the "deleted at" column.
     *
     * @var string
     */
    // const DELETED_AT = 'is_deleted';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'productinventory';

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
    protected $dates = ['quantitymodifieddatetime'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'is_sale',
        'actual_price',
        'total_linked_price',
        'total_linked_saleprice',
        'total_linked_actual_price',
        'total_linked_original_price',
        'quantity_modified_by_full_name',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_deleted' => 'boolean',
        'is_donation' => 'boolean',
        'isdefault' => 'boolean',
        'isshippable' => 'boolean',
        'is_shipping_free' => 'boolean',
        'price' => 'float',
        'quantity' => 'float',
        'quantityrestock' => 'float',
        'saleprice' => 'float',
        'fair_market_value' => 'float',
        'sequence' => 'int',
        'weight' => 'float',
        'cost' => 'float',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'productid')->withTrashed();
    }

    public function membership(): BelongsTo
    {
        return $this->belongsTo(Membership::class);
    }

    public function linkedVariants(): BelongsToMany
    {
        return $this->belongsToMany(Variant::class, 'variant_variant', 'variant_id', 'linked_variant_id')
            ->withPivot('price', 'qty');
    }

    public function quantityModifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'quantitymodifiedbyuserid');
    }

    public function file(): HasOne
    {
        return $this->hasOne(VariantFile::class, 'inventoryid');
    }

    public function media(): MorphToMany
    {
        return $this->morphToMany(Media::class, 'mediable');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'productinventoryid');
    }

    public function stockAdjustments(): HasMany
    {
        return $this->hasMany(StockAdjustment::class, 'variant_id');
    }

    /**
     * Scope: Only variants where there is stock to sell
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeInStock($query)
    {
        $query->where('quantity', '>', 0);
    }

    /**
     * Attribute mask: is_sale
     *
     * @return bool
     */
    public function getIsSaleAttribute()
    {
        return $this->saleprice > 0 && $this->saleprice < $this->price;
    }

    /**
     * Attribute mask: actual_price
     *
     * @return float
     */
    public function getActualPriceAttribute()
    {
        return ($this->is_sale) ? $this->saleprice : $this->price;
    }

    /**
     * Attribute mask: total_linked_actual_price
     *
     * @return float
     */
    public function getTotalLinkedActualPriceAttribute()
    {
        return ($this->is_sale) ? $this->total_linked_saleprice : $this->total_linked_price;
    }

    /**
     * Attribute mask: total_linked_price
     *
     * The selling price of this variant including
     * the price of all it's linked variants.
     *
     * This are for display purposes only.
     * NEVER use this when adding to the cart.
     *
     * @return float|null
     */
    public function getTotalLinkedPriceAttribute($value = null)
    {
        if ($value) {
            return $value;
        }

        return (count($this->linkedVariants) > 0)
            ? $this->price + $this->linkedVariants->sum(function ($v) { return $v->pivot->qty * $v->pivot->price; })
            : null;
    }

    /**
     * Attribute mask: total_linked_saleprice
     *
     * The saleprice price of this variant including
     * the price of all it's linked variants.
     *
     * This are for display purposes only.
     * NEVER use this when adding to the cart.
     *
     * @return float|null
     */
    public function getTotalLinkedSalepriceAttribute($value = null)
    {
        if ($value) {
            return $value;
        }

        return (count($this->linkedVariants) > 0)
            ? $this->saleprice + $this->linkedVariants->sum(function ($v) { return $v->pivot->qty * $v->pivot->price; })
            : null;
    }

    /**
     * Attribute mask: total_linked_original_price
     *
     * The original price of this variant including
     * the original price of all it's linked
     * variants.
     *
     * This are for display purposes only.
     * NEVER use this when adding to the cart.
     *
     * @return float|null
     */
    public function getTotalLinkedOriginalPriceAttribute($value = null)
    {
        if ($value) {
            return $value;
        }

        return (count($this->linkedVariants) > 0)
            ? $this->price + $this->linkedVariants->sum(function ($v) { return $v->pivot->qty * $v->price; })
            : null;
    }

    /**
     * Attribute mask: last_updated_by_full_name
     *
     * @return float|null
     */
    public function getQuantityModifiedByFullNameAttribute()
    {
        return ($this->quantityModifiedBy) ? $this->quantityModifiedBy->full_name : null;
    }

    /**
     * Attribute mask: Shipping Expectation
     *
     * @return string|void|null
     */
    public function getShippingExpectationAttribute()
    {
        $threshold = $this->shipping_expectation_threshold ?? sys_get('shipping_expectation_threshold');

        if (! $this->isshippable || ! is_numeric($threshold)) {
            return;
        }

        if ($threshold < $this->quantity) {
            return $this->shipping_expectation_over ?? sys_get('shipping_expectation_over');
        }

        return $this->shipping_expectation_under ?? sys_get('shipping_expectation_under');
    }

    /**
     * This should be used in conjunction with `checkAvailability` because parent product
     * could be allowing out-of-stock purchases.
     */
    public function getMaximumQuantityAvailableForPurchaseAttribute(): int
    {
        $min = PHP_INT_MAX;

        foreach ($this->linkedVariants as $variant) {
            $min = min(floor($variant->maximumQuantityAvailableForPurchase / $variant->pivot->qty), $min);
        }

        return min($min, $this->quantity, $this->product->available_for_purchase);
    }

    /**
     * Attribute Accessor: Quantity Remaining
     *
     * @return int
     */
    public function getQuantityRemainingAttribute()
    {
        return $this->quantity;
    }

    /**
     * Check the variant availability within the context of a cart.
     *
     * @param int $quantity
     * @return bool
     */
    public function checkAvailability($quantity)
    {
        foreach ($this->linkedVariants as $variant) {
            $available = $variant->checkAvailability($variant->pivot->qty * $quantity);

            if (! $available) {
                return false;
            }
        }

        if ($this->product->outofstock_allow) {
            return true;
        }

        if ($quantity > $this->quantity) {
            return false;
        }

        if ($quantity > $this->product->available_for_purchase) {
            return false;
        }

        return true;
    }

    /**
     * Liquid representation of model.
     */
    public function toLiquid()
    {
        return \Ds\Domain\Theming\Liquid\Drop::factory($this, 'Variant');
    }
}
