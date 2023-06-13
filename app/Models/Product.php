<?php

namespace Ds\Models;

use Ds\Domain\Commerce\Currency;
use Ds\Domain\Theming\Liquid\Liquidable;
use Ds\Eloquent\Hashids;
use Ds\Eloquent\HasMetadata;
use Ds\Eloquent\Metadatable;
use Ds\Eloquent\Permissions;
use Ds\Eloquent\SoftDeleteUserstamp;
use Ds\Eloquent\Userstamps;
use Ds\Enums\ProductType;
use Ds\Illuminate\Database\Eloquent\Auditable;
use Ds\Illuminate\Database\Eloquent\Factories\HasFactory;
use Ds\Illuminate\Database\Eloquent\HasAuditing;
use Ds\Illuminate\Database\Eloquent\Model;
use Ds\Models\Observers\ProductObserver;
use Ds\Models\Traits\HasEmails;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use stdClass;

class Product extends Model implements Auditable, Liquidable, Metadatable
{
    use HasAuditing;
    use HasEmails;
    use HasFactory;
    use Hashids;
    use HasMetadata;
    use Permissions;
    use SoftDeleteUserstamp;
    use SoftDeletes;
    use Userstamps;

    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    const CREATED_AT = 'createddatetime';

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = 'modifieddatetime';

    /**
     * The name of the "created by" column.
     *
     * @var string
     */
    const CREATED_BY = 'createdbyuserid';

    /**
     * The name of the "updated by" column.
     *
     * @var string
     */
    const UPDATED_BY = 'modifiedbyuserid';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'product';

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'available_for_purchase',
        'custom_field_count',
        'is_sale',
        'url',
        'is_image_valid',
        'thumbnail_url',
        'variant_count',
        'is_available_for_sale',
        'goal_progress',
        'goal_count',
        'goal_progress_percent',
        'tribute_types',
        'actualprice',
        'min_variant_price',
        'max_variant_price',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'publish_start_date',
        'publish_end_date',
        'deleted_at',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'allow_check_in' => 'boolean',
        'allow_tribute_notification' => 'integer',
        'allow_tributes' => 'integer',
        'goal_deadline' => 'date',
        'goal_use_dpo' => 'boolean',
        'goalamount' => 'double',
        'goalamount_offset' => 'double',
        'hide_price' => 'boolean',
        'hide_qty' => 'boolean',
        'designation_options' => 'object',
        'is_deleted' => 'boolean',
        'is_tax_receiptable' => 'boolean',
        'is_dcc_enabled' => 'boolean',
        'isclearance' => 'boolean',
        'isenabled' => 'boolean',
        'isfblike' => 'boolean',
        'isfeatured' => 'boolean',
        'isfemale' => 'boolean',
        'ismale' => 'boolean',
        'isnew' => 'boolean',
        'istribute' => 'boolean',
        'limit_sales' => 'integer',
        'outofstock_allow' => 'boolean',
        'price' => 'double',
        'recurring_with_dpo' => 'boolean',
        'saleprice' => 'double',
        'show_in_pos' => 'boolean',
        'ach_only' => 'boolean',
        'min_price' => 'double',
        'tribute_type_ids' => 'array',
    ];

    /**
     * CACHE Attribute Mask: goal_progress
     *
     * @var float|null
     */
    private $goal_progress = null;

    /**
     * CACHE Attribute Mask: goal_count
     *
     * @var int|null
     */
    private $goal_count = null;

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        self::observe(new ProductObserver);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(ProductCategory::class, 'productcategorylink', 'productid', 'categoryid');
    }

    public function promoCodes(): BelongsToMany
    {
        return $this->belongsToMany(PromoCode::class, 'productpromocodeproduct', 'productid', 'promocodeid');
    }

    public function taxes(): BelongsToMany
    {
        return $this->belongsToMany(Tax::class, 'producttaxproduct', 'productid', 'taxid');
    }

    public function customFields(): HasMany
    {
        return $this->hasMany(ProductCustomField::class, 'productid')
            ->orderBy('sequence');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(Variant::class, 'productid')
            ->orderBy('sequence', 'asc');
    }

    public function defaultVariant(): HasOne
    {
        return $this->hasOne(Variant::class, 'productid')
            ->where('isdefault', 1);
    }

    public function recurringPaymentProfiles(): HasMany
    {
        return $this->hasMany(RecurringPaymentProfile::class);
    }

    public function orderItems(): HasManyThrough
    {
        return $this->hasManyThrough(OrderItem::class, Variant::class, 'productid', 'productinventoryid');
    }

    public function paidOrderItems(): HasManyThrough
    {
        return $this->hasManyThrough(OrderItem::class, Variant::class, 'productid', 'productinventoryid')
            ->select('productorderitem.*')
            ->join('productorder', function ($join) {
                $join->on('productorder.id', '=', 'productorderitem.productorderid')
                    ->whereNull('productorder.deleted_at')
                    ->whereNotNull('productorder.confirmationdatetime');
            })->orderBy('productorder.confirmationdatetime', 'desc');
    }

    public function memberships(): BelongsToMany
    {
        return $this->belongsToMany(Membership::class, 'membership_access', 'parent_id', 'membership_id')
            ->where('membership_access.parent_type', '=', 'product');
    }

    public function photo(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'media_id');
    }

    public function pledgeCampaigns(): MorphToMany
    {
        return $this->morphToMany(PledgeCampaign::class, 'pledgable');
    }

    public function pledges(): MorphToMany
    {
        return $this->morphToMany(Pledge::class, 'pledgable');
    }

    /**
     * Attribute Accessor: Designation Options
     */
    public function getDesignationOptionsAttribute(): ?stdClass
    {
        $value = $this->attributes['designation_options'] ?? null;

        if (empty($value)) {
            $value = $this->asJson([
                'type' => 'single_account',
                'default_account' => $this->meta1,
                'designations' => $this->meta1
                    ? [['label' => '', 'account' => $this->meta1, 'is_default' => true]]
                    : [],
            ]);
        }

        return $this->castAttribute('designation_options', $value);
    }

    /**
     * Attribute Accessor: Is Fundraising Form
     *
     * @return bool
     */
    public function getIsFundraisingFormAttribute(): bool
    {
        return $this->type === ProductType::DONATION_FORM;
    }

    /**
     * Attribute Accessor: Thumbnail URL
     *
     * @return bool
     */
    public function getIsTemplateAttribute()
    {
        return $this->type === ProductType::TEMPLATE;
    }

    /**
     * Attribute Accessor: Recurring Type
     *
     * @return bool
     */
    public function getRecurringTypeAttribute(): ?string
    {
        return $this->is_fundraising_form ? 'natural' : null;
    }

    /**
     * Attribute Mutator: Thumbnail URL
     *
     * @return string
     */
    public function getThumbnailUrlAttribute()
    {
        return media_thumbnail($this);
    }

    /**
     * The custom field count.
     *
     * @return int
     */
    public function getCustomFieldCountAttribute()
    {
        return $this->customFields->count();
    }

    /**
     * Attribute mask: price
     *
     * product.price IS DEPRECATED - price is stored on the variant (productinventory) table
     *
     * @return float
     */
    public function getPriceAttribute()
    {
        if ($this->defaultVariant && count($this->defaultVariant->linkedVariants) > 0) {
            return $this->defaultVariant->total_linked_price;
        }

        return $this->defaultVariant->price ?? null;
    }

    /**
     * Attribute mask: saleprice
     *
     * product.saleprice IS DEPRECATED - saleprice is stored on the variant (productinventory) table
     *
     * @return float|null
     */
    public function getSalepriceAttribute()
    {
        if ($this->is_sale && $this->defaultVariant) {
            if (count($this->defaultVariant->linkedVariants) > 0) {
                return $this->defaultVariant->total_linked_saleprice;
            }

            return $this->defaultVariant->saleprice;
        }
    }

    /**
     * Attribute mask: actualprice
     *
     * @return float
     */
    public function getActualpriceAttribute()
    {
        return ($this->is_sale) ? $this->saleprice : $this->price;
    }

    /**
     * Attribute mask: min_variant_price
     *
     * @return float
     */
    public function getMinVariantPriceAttribute()
    {
        return $this->variants->reduce(function ($carry, $variant) {
            $full_price = max($variant->price, $variant->total_linked_actual_price);
            $sale_price = $variant->saleprice ? max($variant->saleprice, $variant->total_linked_sale_price) : 0;
            $actual_price = ($sale_price > 0) ? $sale_price : $full_price;

            return (is_null($carry) or $actual_price < $carry) ? $actual_price : $carry;
        });
    }

    /**
     * Attribute mask: max_variant_price
     *
     * @return float
     */
    public function getMaxVariantPriceAttribute()
    {
        return $this->variants->reduce(function ($carry, $variant) {
            $full_price = max($variant->price, $variant->total_linked_actual_price);
            $sale_price = $variant->saleprice ? max($variant->saleprice, $variant->total_linked_sale_price) : 0;
            $actual_price = ($sale_price > 0) ? $sale_price : $full_price;

            return (is_null($carry) or $actual_price > $carry) ? $actual_price : $carry;
        });
    }

    /**
     * The product sale flag.
     *
     * @return bool
     */
    public function getIsSaleAttribute()
    {
        return ($this->defaultVariant->saleprice ?? 0) > 0;
    }

    /**
     * Tribute Types based on filters in this prdouct
     *
     * @return string
     */
    public function getTributeTypesAttribute()
    {
        // if this product only allows sepecific tributes, return those
        if ($this->tribute_type_ids && count($this->tribute_type_ids) > 0) {
            return TributeType::active()->whereIn('id', $this->tribute_type_ids)->get();
        }

        return reqcache('active_tribute_types', function () {
            return TributeType::active()->get();
        });
    }

    /**
     * The relative URL to the product.
     *
     * @return string
     */
    public function getUrlAttribute()
    {
        if ($this->type === ProductType::DONATION_FORM) {
            return route('donation-forms.show', [$this->code]);
        }

        if ($this->permalink) {
            return "/{$this->permalink}";
        }

        return "/product/{$this->code}/" . Str::slug($this->name);
    }

    /**
     * The absolute URL to the product.
     *
     * @return string
     */
    public function getAbsUrlAttribute()
    {
        return secure_site_url($this->url);
    }

    public function getAdminUrlAttribute(): string
    {
        if ($this->type === ProductType::DONATION_FORM) {
            return route('backend.fundraising.forms.view', [$this->hashid]);
        }

        return route('backend.products.edit', ['i' => $this->id]);
    }

    /**
     * Mutator: share_url
     * The URL to the product for sharing links
     *
     * @return string
     */
    public function getShareUrlAttribute()
    {
        $member = member();

        return $member ? $member->getShareableLink($this->url) : $this->abs_url;
    }

    /**
     * Attribute Accessor: Base Currency
     *
     * @return \Ds\Domain\Commerce\Currency
     */
    public function getBaseCurrencyAttribute($value): Currency
    {
        return new Currency($value);
    }

    /**
     * Attribute Mutator: Base Currency
     *
     * @param string $value
     */
    public function setBaseCurrencyAttribute($value)
    {
        $currency = new Currency($value);

        if ($currency->isDefault()) {
            $this->attributes['base_currency'] = null;
        } else {
            $this->attributes['base_currency'] = $value;
        }
    }

    /**
     * Attribute Mask: The variant count.
     *
     * @return int
     */
    public function getVariantCountAttribute()
    {
        return $this->variants->count();
    }

    /**
     * Attribute Mask: The amount of this product available for sale.
     *
     * @return int
     */
    public function getAvailableForPurchaseAttribute()
    {
        // if we need to limit sales
        if ($this->limit_sales) {
            // get sales stats (should replace with a relationship like $this->orders->count())
            $purchase_stats = product_total_purchases($this->id);

            // grab total purchased, if exists
            $total_purchased = ($purchase_stats) ? $purchase_stats->quantitypurchased : 0;

            // return number left (no lesser than 0)
            return max(0, $this->limit_sales - $total_purchased);
        }

        return $this->outofstock_allow ? 99999999 : max(0, $this->variants()->sum('quantity'));
    }

    /**
     * Attribute Mask: Determine whether this product is available for sale based on a couple conditions.
     *
     * @return bool
     */
    public function getIsAvailableForSaleAttribute($value)
    {
        return $this->available_for_purchase > 0;
    }

    /**
     * Attribute Mask: goal_progress
     *
     * @return float
     */
    public function getGoalProgressAttribute()
    {
        // don't bother going through all the effort of calculating progress
        // if this product doesn't even support goals
        if (! $this->goalamount) {
            return 0.0;
        }

        if (! isset($this->goal_progress)) {
            $progress = product_get_goal_progress($this);
            $this->goal_progress = $progress->progress_amount;
            $this->goal_count = $progress->progress_count;
        }

        return $this->goal_progress;
    }

    /**
     * Attribute Mask: goal_progress
     *
     * @return int
     */
    public function getGoalCountAttribute()
    {
        // don't bother going through all the effort of calculating progress
        // if this product doesn't even support goals
        if (! $this->goalamount) {
            return 0;
        }

        if (! isset($this->goal_count)) {
            $progress = product_get_goal_progress($this);
            $this->goal_progress = $progress->progress_amount;
            $this->goal_count = $progress->progress_count;
        }

        return $this->goal_count;
    }

    /**
     * Attribute Mask: is_image_valid
     *
     * @return bool
     */
    public function getIsImageValidAttribute()
    {
        return media_thumbnail($this);
    }

    /**
     * Attribute Mask: goal_progress_percent
     *
     * @return float
     */
    public function getGoalProgressPercentAttribute()
    {
        // don't bother going through all the effort of calculating progress
        // if this product doesn't even support goals
        if (! $this->goalamount) {
            return 0.0;
        }

        if (! isset($this->goal_progress)) {
            $progress = product_get_goal_progress($this);
            $this->goal_progress = $progress->progress_amount;
            $this->goal_count = $progress->progress_count;
        }

        // return progress in perccent
        return round(($this->goal_progress / $this->goalamount) * 100, 2);
    }

    /**
     * Attribute Mask: goal_days_left
     *
     * @return int|null
     */
    public function getGoalDaysLeftAttribute()
    {
        if ($this->goal_deadline) {
            return $this->goal_deadline->isFuture()
                ? now()->diffInDays($this->goal_deadline)
                : 0;
        }
    }

    /**
     * Attribute Mask: fundraising_page_name
     *
     * @return string
     */
    public function getFundraisingPageNameAttribute($val)
    {
        return empty($val) ? $this->name : $val;
    }

    /**
     * Attribute Mask: fundraising_page_summary
     *
     * @return string
     */
    public function getFundraisingPageSummaryAttribute($val)
    {
        return empty($val) ? $this->summary : $val;
    }

    /**
     * Attribute Mask: tribute_notification_types
     *
     * @return array|null
     */
    public function getTributeNotificationTypesAttribute()
    {
        if ($this->allow_tributes == 0 || $this->allow_tribute_notification == 0) {
            return null;
        }

        if ($this->allow_tribute_notification == 1) {
            return ['email', 'letter'];
        }

        if ($this->allow_tribute_notification == 2) {
            return ['email'];
        }

        if ($this->allow_tribute_notification == 3) {
            return ['letter'];
        }
    }

    /**
     * Get a unique array of filter options used.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function filterOptions()
    {
        return self::whereRaw("IFNULL(author,'') != ''")
            ->orderBy('author')
            ->select('author')
            ->distinct()
            ->pluck('author');
    }

    /**
     * Scope: Include the is_locked indicator (for lists)
     *
     * @param \Illuminate\Database\Query\Builder $query
     */
    public function scopeWithLockCount($query)
    {
        $query->join(DB::raw("(select parent_id as __parent_id, COUNT(*) as lock_count
            from membership_access
            inner join membership on membership.id = membership_access.membership_id
            where membership.deleted_at is null
                and parent_type = 'product'
            group by __parent_id) as _x"), '_x.__parent_id', '=', $this->table . '.id', 'left');
    }

    /**
     * Scope: Only active products (dates, isdeleted, etc)
     *
     * @param \Illuminate\Database\Query\Builder $query
     */
    public function scopeActive($query)
    {
        return $query->where(function ($qry) {
            // is enabled
            $qry->where('product.isenabled', 1);

            // not deleted
            $qry->where('product.is_deleted', 0);

            // start date
            $qry->where(function ($qry) {
                return $qry->whereNull('product.publish_start_date')
                    ->orWhere(DB::raw("concat(`product`.`publish_start_date`,' 00:00:00')"), '<=', fromUtc('now'));
            });

            // end date
            $qry->where(function ($qry) {
                return $qry->whereNull('product.publish_end_date')
                    ->orWhere(DB::raw("concat(`product`.`publish_end_date`,' 23:59:59')"), '>=', fromUtc('now'));
            });

            return $qry;
        });
    }

    public function scopeDonationForms(Builder $query): void
    {
        $query->where('type', ProductType::DONATION_FORM);
    }

    public function scopeWithoutDonationForms(Builder $query): void
    {
        $query->where(function ($query) {
            $query->whereNull('type')->orWhere('type', '!=', ProductType::DONATION_FORM);
        });
    }

    public function scopeWhereDefaultDonationForm(Builder $query): void
    {
        $query->whereHas('metadataRelation', function ($query) {
            $query->where('key', 'donation_forms_is_default_form');
            $query->where('value', true);
        });
    }

    /**
     * Scope: Templates
     *
     * @param \Illuminate\Database\Query\Builder $query
     */
    public function scopeTemplates($query)
    {
        return $query->where('type', ProductType::TEMPLATE);
    }

    public function scopeWithoutTemplates(Builder $query): Builder
    {
        return $query->where(function ($query) {
            $query->whereNull('type')
                ->orWhere('type', '!=', ProductType::TEMPLATE);
        });
    }

    /**
     * Scope: Only products that can be receipted (tax receipts).
     *
     * @param \Illuminate\Database\Query\Builder $query
     */
    public function scopeReceiptable($query)
    {
        return $query->where('is_tax_receiptable', 1);
    }

    /**
     * Scope: Only active products (dates, isdeleted, etc)
     * POS allows products even if they are isenabled = false
     *
     * @param \Illuminate\Database\Query\Builder $query
     */
    public function scopeActiveForPOS($query)
    {
        return $query->where(function ($qry) {
            // not deleted
            $qry->where('is_deleted', 0);

            // visible in POS
            $qry->where('show_in_pos', 1);

            // if pos_allow_expired_products
            if (! sys_get('pos_allow_expired_products')) {
                // start date
                $qry->where(function ($qry) {
                    return $qry->whereNull('product.publish_start_date')
                        ->orWhere(DB::raw("concat(`product`.`publish_start_date`,' 00:00:00')"), '<=', now());
                });

                // end date
                $qry->where(function ($qry) {
                    return $qry->whereNull('product.publish_end_date')
                        ->orWhere(DB::raw("concat(`product`.`publish_end_date`,' 23:59:59')"), '>=', now());
                });
            }

            return $qry;
        });
    }

    /**
     * Scope: Restrict this query to public products OR private
     * products that belong to this member's membership level.
     *
     * @param \Illuminate\Database\Query\Builder $query
     */
    public function scopeSecuredByMembership($query)
    {
        // left join on memberships
        $query->leftJoin('membership_access AS _ax', function ($join) {
            $join->on('_ax.parent_id', '=', 'product.id');
            $join->where('_ax.parent_type', '=', 'product');
        });

        // membership soft deletes
        $query->leftJoin('membership AS _axm', function ($join) {
            $join->on('_axm.id', '=', '_ax.membership_id');
            $join->whereNull('_axm.deleted_at');
        });

        // restrict products if they are assigned to a membership level
        // allow products if the user is logged in and the product matches a membership level
        $query->where(function ($query) {
            // public products
            $query->whereNull('_axm.id');

            // private content
            if (member()) {
                $activeGroups = member()->activeGroups();

                if ($activeGroups->count() > 0) {
                    $query->orWhere(function ($q) use ($activeGroups) {
                        $q->whereNotNull('_axm.id');
                        $q->whereIn('_ax.membership_id', $activeGroups->pluck('id')->all());
                    });
                }
            }
        });
    }

    /**
     * Use in public-facing product lists.
     *
     * Must wrap EVERY in public product lists.
     *
     * --
     * In an attempt to refactor frontend controllers to reference proper
     * models, a couple of issues were found.
     *
     * COLUMN CONFLICT: min_price
     * Product lists on front end refer to min_price and max_price which
     * are supposed to represent the min and max price of the collective
     * variants for the given product.  HOWEVER, min_price is an actual
     * column on the product table representing the min donation value
     * required for a product.
     *
     * COLUMN CONFLICT: max_price
     * Product lists on front end refer to max_price which is not a column
     * and not something we want to create an accessor/mask for. So we
     * need to fake its existance only for public lists.
     *
     * !!!!!!!!!!!!!!!!!!!
     * !!!!!! TO-DO !!!!!!
     * !!!!!!!!!!!!!!!!!!!
     *
     * - fix all client templates to references min_variant_price and
     *   max_variant_price
     * - add product.min_variant_price, product.max_variant_price to
     *   the db schema
     * - add an observer on Variants that auto update columns on the
     *   product table:
     *     - product.price
     *     - product.saleprice
     *     - product.min_variant_price
     *     - product.max_variant_price
     *
     * @param \Illuminate\Support\Collection $products a collection of products
     */
    public static function legacyTemplateFixes(Collection $products)
    {
        // go over each $product
        foreach ($products as $product) {
            $product->min_price = $product->min_variant_price;
            $product->max_price = $product->max_variant_price;
        }

        // return updated list
        return $products;
    }

    public static function getTemplates()
    {
        return reqcache('template-suffixes:product#theming', function () {
            return app('theme')->getAssetList('templates/product.*liquid')
                ->map(function ($key) {
                    $suffix = preg_replace('#templates/product(.*)\.liquid#', '$1', $key);

                    try {
                        $thumbnail = app('theme')->asset('assets/templates/product' . $suffix . '.png')->public_url;
                    } catch (ModelNotFoundException $e) {
                        $thumbnail = jpanel_asset_url('images/product.unknown-template.png');
                    }
                    $suffix = trim($suffix, '.');

                    return [
                        'name' => $suffix == '' ? 'Default' : ucwords(strtr($suffix, '-', ' ')),
                        'suffix' => $suffix,
                        'thumbnail' => $thumbnail,
                    ];
                })->sortBy('suffix')->toArray();
        });
    }

    /**
     * Liquid representation of model.
     */
    public function toLiquid()
    {
        return \Ds\Domain\Theming\Liquid\Drop::factory($this, 'Product');
    }

    /**
     * Make a copy of this product
     *
     * @return Product
     */
    public function makeACopy()
    {
        $newProduct = $this->replicate();
        $newProduct->name = "(COPY) {$this->name}";
        $newProduct->code = "{$this->code}-COPY";
        $newProduct->createddatetime = fromUtc('now');
        $newProduct->modifieddatetime = fromUtc('now');
        $newProduct->createdbyuserid = user('id');
        $newProduct->modifiedbyuserid = user('id');
        $newProduct->permalink = null;
        $newProduct->isenabled = 0;
        $newProduct->save();

        $metadata = $this->getMetadata()->map(fn ($m) => $m->replicate(['metadatable_id', 'metadatable_type']));
        $newProduct->metadataRelation()->saveMany($metadata);
        $newProduct->save();

        // copy product categorys
        db_query(sprintf(
            'INSERT INTO productcategorylink (productid, categoryid) SELECT %d, categoryid FROM productcategorylink WHERE productid = %d',
            $newProduct->id,
            $this->id
        ));

        // copy taxes
        db_query(sprintf(
            'INSERT INTO producttaxproduct (productid, taxid) SELECT %d, taxid FROM producttaxproduct WHERE productid = %d',
            $newProduct->id,
            $this->id
        ));

        // copy stock
        foreach ($this->variants as $variant) {
            $newVariant = $variant->replicate();
            $newVariant->productid = $newProduct->id;
            $newVariant->save();
        }

        // copy custom fields
        foreach ($this->customFields as $customField) {
            $newCustomField = $customField->replicate();
            $newCustomField->productid = $newProduct->id;
            $newCustomField->save();
        }

        return $newProduct;
    }
}
