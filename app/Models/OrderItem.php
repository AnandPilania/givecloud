<?php

namespace Ds\Models;

use Ds\Domain\Shared\DateTime;
use Ds\Domain\Shared\Exceptions\MessageException;
use Ds\Domain\Sponsorship\Models\Sponsor;
use Ds\Domain\Sponsorship\Models\Sponsorship;
use Ds\Domain\Theming\Liquid\Liquidable;
use Ds\Eloquent\Hashids;
use Ds\Eloquent\HasMetadata;
use Ds\Eloquent\Metadatable;
use Ds\Illuminate\Database\Eloquent\Factories\HasFactory;
use Ds\Illuminate\Database\Eloquent\Model;
use Ds\Models\Observers\OrderItemObserver;
use Ds\Models\Traits\HasExternalReferences;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

class OrderItem extends Model implements Liquidable, Metadatable
{
    use Hashids;
    use HasExternalReferences;
    use HasFactory;
    use HasMetadata;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'productorderitem';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'description',
        'image_thumb',
        'is_eligible_for_dcc',
        'is_locked',
        'is_recurring',
        'is_price_reduced',
        'undiscounted_price',
        'locked_original_price',
        'locked_variants_original_price',
        'locked_variants_price',
        'locked_variants_total',
        'payment_string',
        'public_url',
        'recurring_description',
        'reference',
        'total',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'sponsorship_expired_at',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'dpo_tribute_id' => 'integer',
        'gift_aid' => 'boolean',
        'is_tribute' => 'boolean',
        'price' => 'double',
        'qty' => 'integer',
        'recurring_amount' => 'double',
        'recurring_day' => 'integer',
        'recurring_day_of_week' => 'integer',
        'recurring_with_dpo' => 'boolean',
        'recurring_with_initial_charge' => 'boolean',
        'recurring_cycles' => 'integer',
        'recurring_starts_on' => 'date',
        'recurring_ends_on' => 'date',
        'sponsorship_is_expired' => 'boolean',
        'total_tax_amt' => 'double',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'productorderid',
        'productinventoryid',
        'price',
        'original_price',
        'qty',
        'locked_to_item_id',
    ];

    /**
     * The attributes that are not arrayable.
     *
     * @var array
     */
    protected $notArrayable = [
        'order',
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        self::observe(new OrderItemObserver);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'productorderid')
            ->withSpam()
            ->withTrashed();
    }

    /**
     * A relationship that returns all the siblings items in the order.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'productorderid', 'productorderid')
            ->where('productorderitem.id', '!=', $this->id);
    }

    /**
     * OrderItemFile
     */
    public function file(): HasOne
    {
        return $this->hasOne(OrderItemFile::class, 'orderitemid');
    }

    public function lockedToItem(): BelongsTo
    {
        return $this->belongsTo(self::class, 'locked_to_item_id');
    }

    /**
     * A relationship that returns all the siblings items in the order.
     */
    public function lockedItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'locked_to_item_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(Variant::class, 'productinventoryid')->withTrashed();
    }

    /**
     * Charity staff can change the product/variant that an
     * order line item points to AFTER the order is placed.
     * This is often done because someone registered for the
     * wrong event or class.
     *
     * OriginalVariant is the originally purchased Variant.
     */
    public function originalVariant(): BelongsTo
    {
        return $this->belongsTo(Variant::class, 'original_variant_id');
    }

    public function fields(): BelongsToMany
    {
        return $this->belongsToMany(ProductCustomField::class, 'productorderitemfield', 'orderitemid', 'fieldid')
            ->withTrashed()
            ->withPivot('value', 'original_value', 'id')
            ->orderBy('sequence');
    }

    public function sponsorship(): BelongsTo
    {
        return $this->belongsTo(Sponsorship::class);
    }

    public function sponsor(): HasOne
    {
        return $this->hasOne(Sponsor::class);
    }

    /**
     * Relationship: PromoCode
     */
    public function promo(): BelongsTo
    {
        return $this->belongsTo(PromoCode::class, 'promocode', 'code');
    }

    public function tribute(): HasOne
    {
        return $this->hasOne(Tribute::class, 'order_item_id');
    }

    public function tributeType(): BelongsTo
    {
        return $this->belongsTo(TributeType::class);
    }

    public function taxes(): BelongsToMany
    {
        return $this->belongsToMany(Tax::class, 'productorderitemtax', 'orderitemid', 'taxid')
            ->withPivot('amount');
    }

    public function recurringPaymentProfile(): HasOne
    {
        return $this->hasOne(RecurringPaymentProfile::class, 'productorderitem_id');
    }

    public function checkIns(): HasMany
    {
        return $this->hasMany(CheckIn::class);
    }

    public function fundraisingPage(): BelongsTo
    {
        return $this->belongsTo(FundraisingPage::class, 'fundraising_page_id');
    }

    public function fundraisingPageAccount(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'fundraising_member_id');
    }

    public function groupAccount(): HasOne
    {
        return $this->hasOne(GroupAccount::class, 'order_item_id');
    }

    /**
     * Scope: paid items
     */
    public function scopePaid($qry)
    {
        return $qry->whereHas('order', function ($q) {
            $q->paid();
        });
    }

    public function getIsDownloadableAttribute(): bool
    {
        return (bool) $this->download_link;
    }

    public function getDownloadLinkAttribute(): ?string
    {
        if (! $this->file) {
            return null;
        }

        return $this->file->url;
    }

    /**
     * Attribute mask: Requires ACH
     *
     * @return bool
     */
    public function getRequiresAchAttribute()
    {
        return $this->variant->product->ach_only ?? false;
    }

    /**
     * Attribute Mask: Requires Shipping
     *
     * @return bool
     */
    public function getRequiresShippingAttribute(): bool
    {
        if (sys_get('shipping_linked_items') === 'bundle' && $this->is_locked) {
            return false;
        }

        return isset($this->variant) && $this->variant->isshippable;
    }

    /**
     * Attribute Mask: Has Free Shipping
     *
     * @return bool
     */
    public function getHasFreeShippingAttribute(): bool
    {
        return $this->requires_shipping && isset($this->variant) && $this->variant->is_shipping_free;
    }

    /**
     * Attribute Accessor: GL Code
     */
    public function getGlCodeAttribute(): ?string
    {
        return $this->general_ledger_code
            ?? $this->metadata->dp_gl_code
            ?? $this->sponsorship->meta1
            ?? $this->variant->metadata->dp_gl_code
            ?? $this->variant->product->designation_options->default_account
            ?? $this->variant->product->meta1
            ?? null;
    }

    /**
     * Attribute Accessor: Designation
     */
    public function getDesignationAttribute(): ?string
    {
        $options = $this->variant->product->designation_options ?? null;

        if (empty($options) || $options->type !== 'supporters_choice') {
            return null;
        }

        $designations = collect($options->designations ?? [])
            ->pluck('label', 'account');

        return $designations[$this->gl_code] ?? null;
    }

    /**
     * Attribute Mutator: GL Code
     */
    public function setGlCodeAttribute(?string $value): void
    {
        $this->attributes['general_ledger_code'] = $value ?: $this->gl_code;
    }

    /**
     * Attribute Mask: is_receiptable
     */
    public function getIsReceiptableAttribute(): bool
    {
        if ($this->variant && $this->variant->product->is_tax_receiptable) {
            return true;
        }

        if ($this->sponsorship_id && sys_get('sponsorship_tax_receipts')) {
            return true;
        }

        return false;
    }

    /**
     * Attribute Mask: receiptable_amount
     *
     * @return float
     */
    public function getReceiptableAmountAttribute()
    {
        if ($this->variant->product->is_tax_receiptable ?? false) {
            return max(0, $this->total - ($this->variant->fair_market_value * $this->qty)) + $this->dcc_amount;
        }

        if ($this->sponsorship_id && sys_get('sponsorship_tax_receipts')) {
            return $this->total + $this->dcc_amount;
        }

        return 0.0;
    }

    /**
     * Attribute mask: is_locked
     *
     * Is this item locked to another item in the cart?
     * This means - if the item this item belongs to is
     * deleted, this item should be deleted from the
     * order as well.
     *
     * @return bool
     */
    public function getIsLockedAttribute()
    {
        return (bool) ($this->locked_to_item_id);
    }

    /**
     * Attribute mask: locked_original_price
     *
     * If this item locked to another item in the cart,
     * this will return the original locked price for
     * the purpose of discounting it.
     *
     * @return float|null
     */
    public function getLockedOriginalPriceAttribute()
    {
        return $this->original_price;
    }

    /**
     * Attribute Accessor: Admin Link
     */
    public function getAdminLinkAttribute(): ?string
    {
        if ($this->fundraisingPage && user()->can('fundraisingpages.edit')) {
            return route('backend.fundraising-pages.view', $this->fundraisingPage);
        }

        if ($this->variant && $this->variant->product && user()->can('product.view')) {
            if ($this->order->isForFundraisingForm()) {
                return route('backend.fundraising.forms.view', [$this->variant->product->hashid]);
            }

            return route('backend.products.edit', ['i' => $this->variant->product->getKey()]);
        }

        if ($this->sponsorship && user()->can('sponsorship.view')) {
            return route('backend.sponsorship.view', $this->sponsorship);
        }

        return null;
    }

    /**
     * Attribute mask: image_thumb
     *
     * @return string|null
     */
    public function getImageThumbAttribute()
    {
        if ($this->fundraisingPage) {
            return media_thumbnail($this->fundraisingPage->photo);
        }

        if ($this->variant && $this->variant->product) {
            if ($this->variant->product->is_fundraising_form) {
                return media_thumbnail(
                    $this->variant->product->metadata['donation_forms_social_preview_image'] ?? $this->variant->product->metadata['donation_forms_background_image'],
                    ['50x50', 'crop' => 'entropy'],
                );
            }

            return media_thumbnail($this->variant->product);
        }

        if ($this->sponsorship) {
            return media_thumbnail($this->sponsorship);
        }
    }

    /**
     * Attribute mask: description
     *
     * @return string|null
     */
    public function getDescriptionAttribute()
    {
        if ($this->fundraisingPage) {
            return $this->fundraisingPage->title;
        }

        if ($this->variant) {
            return $this->variant->product->name . (($this->variant->variantname) ? ' - ' . $this->variant->variantname : '');
        }

        if ($this->sponsorship) {
            return $this->sponsorship->first_name . ' ' . $this->sponsorship->last_name;
        }
    }

    public function getLongDescriptionAttribute()
    {
        if ($this->sponsorship) {
            return 'Sponsorship - ' . $this->sponsorship->display_name;
        }

        if ($membership = data_get($this, 'variant.membership')) {
            return $this->description . ' (' . $membership->name . ')';
        }

        if ($this->fundraisingPage) {
            return $this->description . ' (' . $this->fundraisingPage->title . ')';
        }

        return $this->description . ' - ' . $this->code;
    }

    /**
     * Attribute mask: Code
     *
     * @return bool
     */
    public function getCodeAttribute()
    {
        if (isset($this->variant)) {
            return $this->variant->sku ?: $this->variant->product->code ?? null;
        }

        return $this->sponsorship->reference_number ?? null;
    }

    /**
     * Attribute mask: reference
     *
     * @return bool
     */
    public function getReferenceAttribute()
    {
        if ($this->fundraisingPage) {
            return $this->fundraisingPage->url;
        }

        return $this->code;
    }

    /**
     * Attribute mask: public_url
     *
     * @return string|null
     */
    public function getPublicUrlAttribute()
    {
        if ($this->fundraisingPage) {
            return $this->fundraisingPage->absolute_url;
        }

        if ($this->variant) {
            return secure_site_url($this->variant->product->url);
        }

        if ($this->sponsorship) {
            return secure_site_url($this->sponsorship->url);
        }
    }

    /**
     * Attribute mask: payment_string
     *
     * @return string
     */
    public function getPaymentStringAttribute($payment_string = null)
    {
        // If this order item is a recurring payment.
        if ($this->is_recurring) {
            // Try accessing this item's recurring payment profile.
            // (will only exist if the order has already been completed)
            $profile = $this->recurringPaymentProfile;

            // If no recurring profile was found (order is not yet completed),
            // we need to synthesize a payment profile so we can guestimate
            // a profile start date.
            if (! $profile) {
                $profile = new RecurringPaymentProfile;
                $profile->billing_period = $this->recurring_frequency;

                $startDate = $profile->getFirstPossibleStartDate(
                    $this->variant->product->recurring_type ?? sys_get('rpp_default_type'),
                    $this->recurring_day,
                    $this->recurring_day_of_week,
                    $this->recurring_with_initial_charge ? 'one-time' : null,
                    $this->recurring_starts_on,
                    $this->order()->first()->confirmationdatetime ?? 'today'
                );

            // If a recurring payment profile does exist, use it's start date.
            // This ensures that "Renotify Customer" returns consistent messaging.
            } else {
                $startDate = $profile->profile_start_date;
            }

            $startDate = fromLocal($startDate);

            // Start building the payment string (ex: $25.00 CAD).
            $paymentString = money($this->recurring_amount + $this->dcc_recurring_amount, $this->order->currency) . ' ' . $this->order->currency->code;

            // Build the rest of the payment string based on the recurring period.
            if ($profile->billing_period === 'Week') {
                return $paymentString . $this->buildPaymentString($startDate, 'payments.period.weekly', 'dates.day.suffixed');
            }

            if ($profile->billing_period === 'SemiMonth') {
                return $paymentString . $this->buildPaymentString($startDate, 'payments.period.semi_monthly', 'dates.day.suffixed');
            }

            if ($profile->billing_period === 'Quarter') {
                return $paymentString . $this->buildPaymentString($startDate, 'payments.period.quarterly', 'dates.month.suffixed');
            }

            if ($profile->billing_period === 'Month') {
                return $paymentString . $this->buildPaymentString($startDate, 'payments.period.monthly', 'dates.month.suffixed');
            }

            if ($profile->billing_period === 'SemiYear') {
                return $paymentString . $this->buildPaymentString($startDate, 'payments.period.semi_yearly', 'dates.month.suffixed');
            }

            if ($profile->billing_period === 'Year') {
                return $paymentString . $this->buildPaymentString($startDate, 'payments.period.yearly', 'dates.month.suffixed');
            }
        }

        // If this item is NOT a recurring payment,
        // return a simple "one-time" payment description.
        return money($this->price, $this->order->currency) . ' ' . $this->order->currency->code . ' ' . trans('payments.period.one_time');
    }

    /**
     * Attribute mask: Is Donation
     *
     * @return bool
     */
    public function getIsDonationAttribute()
    {
        return $this->variant->is_donation ?? true;
    }

    public function getIsFundraisingFormUpgradeAttribute(): bool
    {
        return $this->variant && $this->is_locked && $this->lockedToItem->upgraded_to_recurring;
    }

    /**
     * Attribute mask: is_price_reduced
     *
     * Easy accessor to let us know when the price in the cart is LOWER than
     * regular price. This happens when:
     *
     * - product.isdonation = false (always) - a donation can't be on sale
     *
     * - variant.saleprice > 0, which means $this->actual_price is LT regular
     *   price
     *
     * - item.price < item.variant.price because a PROMO CODE is overriding
     *   the value of item.price
     *
     * @return bool
     */
    public function getIsPriceReducedAttribute()
    {
        if ($this->variant) {
            if ($this->is_donation) {
                return false;
            }

            if ($this->is_recurring && ! $this->recurring_with_initial_charge) {
                return $this->recurring_amount < $this->undiscounted_price;
            }

            return $this->price < $this->undiscounted_price;
        }

        return false;
    }

    /**
     * Attribute Accessor: Undiscounted Price
     *
     * @return float
     */
    public function getUndiscountedPriceAttribute()
    {
        return $this->original_price + $this->discount;
    }

    /**
     * Attribute Accessor: is_eligible_for_dcc
     *
     * @return bool
     */
    public function getIsEligibleForDccAttribute()
    {
        // Eligible, if its already enabled - even if site wide setting is off
        if ($this->dcc_eligible) {
            return true;
        }

        // Ineligible, if the site-wide setting is off
        if (! sys_get('dcc_enabled')) {
            return false;
        }

        // Eligible, if the attached product has it enabled
        if ($this->variant && $this->variant->product && $this->variant->product->is_dcc_enabled) {
            return true;
        }

        // Eligible, if the Item is related to a Sponsorship and the DCC sponsorship option is turned on
        if ($this->sponsorship && sys_get('dcc_enabled_on_sponsorships')) {
            return true;
        }

        return false;
    }

    /**
     * Attribute mask: total
     *
     * @return float
     */
    public function getTotalAttribute()
    {
        // make sure we have a valid qty (it should never be lower than 1)
        return round(max(1, $this->qty) * $this->price, 2);
    }

    /**
     * Attribute Accessor: Original Total
     *
     * @return float
     */
    public function getOriginalTotalAttribute()
    {
        return round(max(1, $this->qty) * $this->original_price, 2);
    }

    /**
     * Attribute mask: recurring_description
     *
     * EXAMPLES:
     * "$23/mth starting Jul 1st, 2017"
     * "$23/week starting Friday, Jul 1st, 2017"
     *
     * @return string|null
     */
    public function getRecurringDescriptionAttribute()
    {
        // if it has a recurring payment profile
        if ($this->recurringPaymentProfile) {
            return $this->recurringPaymentProfile->payment_string .
                ' starting ' .
                // optionally, include weekday
                (
                    (in_array($this->recurringPaymentProfile->billing_period, ['Week', 'SemiMonth'])) ?
                    $this->recurringPaymentProfile->profile_start_date->format('l') . ', ' :
                    ''
                ) .
                $this->recurringPaymentProfile->profile_start_date->format('F jS, Y');
        }

        // if not, guess at the starting date
        if ($this->is_recurring) {
            return $this->payment_string;
        }

        return null;
    }

    /**
     * Attribute mask: is_recurring
     *
     * @return bool
     */
    public function getIsRecurringAttribute()
    {
        return $this->recurring_frequency ? true : false;
    }

    /**
     * Attribute mask: locked_variants_original_price
     *
     * @return float
     */
    public function getLockedVariantsOriginalPriceAttribute()
    {
        return $this->original_price + $this->lockedItems->sum('original_price');
    }

    /**
     * Attribute mask: locked_variants_price
     *
     * @return float
     */
    public function getLockedVariantsPriceAttribute()
    {
        return $this->price + $this->lockedItems->sum('price');
    }

    /**
     * Attribute Accessor: Locked Variants Undiscounted Price
     *
     * @return float
     */
    public function getLockedVariantsUndiscountedPriceAttribute()
    {
        return $this->locked_variants_original_price + $this->lockedItems->sum('discount');
    }

    /**
     * Attribute mask: locked_variants_total
     *
     * @return float
     */
    public function getLockedVariantsTotalAttribute()
    {
        return $this->total + $this->lockedItems->sum('total');
    }

    /**
     * Scope: gift_aid eligible items
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeGiftAid($query)
    {
        $query->where('gift_aid', 1);
    }

    public function buildPaymentString(DateTime $startDate, string $period, string $dateFormat): string
    {
        return implode(' ', [
            trans($period),
            trans('payments.period.starting'),
            $startDate->format(trans($dateFormat)),
        ]);
    }

    /**
     * Create a sponsor from this item.
     *
     * @param string $source
     * @param bool $match_existing_sponsorship
     * @return string
     */
    public function createSponsor($source = 'Website', $match_existing_sponsorship = false)
    {
        $this->loadLoaded('order');

        // make sure there is a sponsorship record
        if (! (is_numeric($this->sponsorship_id) && $this->sponsorship_id > 0)) {
            throw new MessageException('Failed to create sponsor record. Contribution item id ' . $this->id . ' is not linked to any sponsorship records.');
        }

        // make sure there is a member record
        if (! (is_numeric($this->order->member_id) && $this->order->member_id > 0)) {
            throw new MessageException('Failed to create sponsor record. Contribution id ' . $this->order->id . ' does not have an account associated with it.');
        }

        // does a sponsor already exist for this sponsorship?
        $existing_sponsorship = $this->sponsor()
            ->where('member_id', $this->order->member_id)
            ->active()
            ->first();

        if ($existing_sponsorship) {
            return $existing_sponsorship;
        }

        // if there's an existing sponsor that doesn't
        // belong to or any order, AND the
        // $match_existing_sponsor flag is TRUE, USE it
        if ($match_existing_sponsorship) {
            $s = Sponsor::where('sponsorship_id', $this->sponsorship_id)
                ->where('member_id', $this->order->member_id)
                ->whereNull('order_item_id')
                ->active()
                ->first();

            if ($s) {
                $s->order_item_id = (int) $this->id;
                $s->save();

                return $s;
            }
        }

        // create a brand new sponsor
        $s = new Sponsor;
        $s->sponsorship_id = (int) $this->sponsorship_id;
        $s->member_id = (int) $this->order->member_id;
        $s->source = $source;
        $s->started_at = $this->order->confirmationdatetime;
        $s->started_by = $this->order->member_id;
        $s->order_item_id = (int) $this->id;
        $s->save();

        // send notification email if new sponsor was created
        event(new \Ds\Domain\Sponsorship\Events\SponsorWasStarted($s, [
            'do_not_send_email' => $source === 'Import',
        ]));

        // return new record
        return $s;
    }

    /**
     * Add appropriate tax records to this item in the order.
     */
    public function applyTaxes()
    {
        // clear any pre-existing taxes
        $this->unapplyTaxes();

        // if this item isn't linked to a product, bail
        // (for example: sponsorship)
        if (! $this->variant) {
            return;
        }

        // if the order has shippable items, use the ship address
        // to calculate taxes

        // point of sale
        if ($this->order->is_pos) {
            $tax_city = $this->order->tax_city;
            $tax_state = $this->order->tax_state;
            $tax_country = $this->order->tax_country;

        // checkout
        } else {
            // try using shipping address
            if ($this->order->shippable_items > 0) {
                $tax_city = $this->order->shipcity;
                $tax_state = $this->order->shipstate;
                $tax_country = $this->order->shipcountry;

            // otherwise use the billing address
            } else {
                $tax_city = $this->order->billingcity;
                $tax_state = $this->order->billingstate;
                $tax_country = $this->order->billingcountry;
            }
        }

        // if we have a city to use, include city
        // in the tax calculation
        if ($tax_city) {
            DB::insert(
                DB::raw("INSERT INTO productorderitemtax (orderitemid, taxid, amount)
                    SELECT :item_id, t.id, (:item_total)*(t.rate/100)
                    FROM producttax t
                    INNER JOIN producttaxproduct pt ON pt.taxid = t.id
                    INNER JOIN producttaxregion tr ON tr.taxid = t.id
                    INNER JOIN region r ON r.id = tr.regionid
                    WHERE pt.productid = :product_id
                        AND r.code = :tax_state
                        AND r.country = :tax_country
                        AND t.deleted_at IS NULL
                        AND (IFNULL(t.city,'') LIKE :tax_city OR IFNULL(t.city,'') = '')"),
                [
                    'item_id' => $this->id,
                    'item_total' => $this->total,
                    'product_id' => $this->variant->product->id,
                    'tax_state' => ($this->order->is_pos) ? $this->order->tax_state : $tax_state,
                    'tax_country' => ($this->order->is_pos) ? $this->order->tax_country : $tax_country,
                    'tax_city' => '%' . $tax_city . '%',
                ]
            );

        // otherwise, exclude all tax rules that
        // reference a city `(IFNULL(t.city,'') = '')`
        } else {
            DB::insert(
                DB::raw("INSERT INTO productorderitemtax (orderitemid, taxid, amount)
                    SELECT :item_id, t.id, (:item_total)*(t.rate/100)
                    FROM producttax t
                    INNER JOIN producttaxproduct pt ON pt.taxid = t.id
                    INNER JOIN producttaxregion tr ON tr.taxid = t.id
                    INNER JOIN region r ON r.id = tr.regionid
                    WHERE pt.productid = :product_id
                        AND r.code = :tax_state
                        AND r.country = :tax_country
                        AND t.deleted_at IS NULL
                        AND (IFNULL(t.city,'') = '')"),
                [
                    'item_id' => $this->id,
                    'item_total' => $this->total,
                    'product_id' => $this->variant->product->id,
                    'tax_state' => ($this->order->is_pos) ? $this->order->tax_state : $tax_state,
                    'tax_country' => ($this->order->is_pos) ? $this->order->tax_country : $tax_country,
                ]
            );
        }

        if ($this->relationLoaded('taxes')) {
            $this->load('taxes');
        }
    }

    /**
     * Clear all tax records associated with this item in the order.
     */
    public function unapplyTaxes()
    {
        $this->taxes()->detach();

        if ($this->relationLoaded('taxes')) {
            $this->load('taxes');
        }
    }

    /**
     * Apply any group/membership related to this order item.
     */
    public function applyGroup()
    {
        // be sure a membership exists
        if (! $this->variant->membership) {
            throw new MessageException('No membership exists on this contribution item.');
        }

        // add group
        $this->order->member->addGroup($this->variant->membership, $this->order->createddatetime, $this);
    }

    /**
     * Update the variant this item is linked to AFTER the
     * the order has been placed.
     *
     * @return self
     */
    public function changeVariant($variant)
    {
        $variant = Variant::findOrFail($variant);

        $this->productinventoryid = $variant->id;
        $this->save();

        $this->load('variant.product');

        if ($this->recurringPaymentProfile) {
            $this->recurringPaymentProfile->description = $this->description;
            $this->recurringPaymentProfile->productinventory_id = $this->productinventoryid;
            $this->recurringPaymentProfile->product_id = $this->variant->productid;
            $this->recurringPaymentProfile->save();
        }

        return $this;
    }

    /**
     * Set the quantity.
     *
     * @param int $quantity
     */
    public function setQuantity($quantity)
    {
        if ($this->order->confirmationdatetime) {
            throw new MessageException('Cannot change the quantity of an item that belongs to a completed contribution.');
        }

        if ($this->locked_to_item_id) {
            throw new MessageException('Cannot change the quantity of an item that is part of a bundle.');
        }

        if ($quantity < 1) {
            throw new MessageException('There must be at least one of the item.');
        }

        if ($quantity > 1 && $this->is_donation) {
            throw new MessageException("The quantity of a donation item can't be greater than one.");
        }

        // need to include the quantity of this variant that belongs to other
        // order items in this order since the availability check only looks at sales
        if ($this->variant) {
            $quantityInCart = $this->order->items
                ->where('productinventoryid', $this->productinventoryid)
                ->whereNotIn('id', [$this->id])
                ->sum('qty');

            if (! $this->variant->checkAvailability($quantity + $quantityInCart)) {
                if ($this->variant->maximumQuantityAvailableForPurchase > 0) {
                    throw new MessageException(trans_choice('frontend/cart.limited_stock', $this->variant->maximumQuantityAvailableForPurchase));
                }

                throw new MessageException(trans('frontend/cart.no_stock'));
            }
        }

        $this->qty = $quantity;
        $this->save();

        // There's currently no way to track a locked item back to the linked
        // variant in the pivot table where the quantity is stored. As a result
        // we need to remove all the locked items in the cart and re-add the
        // linked variants for the variant.
        if ($this->variant->linkedVariants) {
            $this->lockedItems()->delete();

            foreach ($this->variant->linkedVariants as $variant) {
                OrderItem::create([
                    'productorderid' => $this->productorderid,
                    'productinventoryid' => $variant->id,
                    'price' => $variant->pivot->price,
                    'original_price' => $variant->pivot->price,
                    'qty' => $variant->pivot->qty * $this->qty,
                    'locked_to_item_id' => $this->id,
                ]);
            }

            $this->load('lockedItems');
        }

        $this->order->calculate();
        $this->order->reapplyPromos();

        return $this;
    }

    /**
     * Set the amount.
     *
     * @param float $amount
     */
    public function setAmount($amount)
    {
        if ($this->order->confirmationdatetime) {
            throw new MessageException('Cannot change the amount of an item that belongs to a completed contribution.');
        }

        if ($this->locked_to_item_id) {
            throw new MessageException('Cannot change the amount of an item that is part of a bundle.');
        }

        if ($this->is_donation && $amount < 0) {
            throw new MessageException('Donation must be greater than zero.');
        }

        if (! $this->is_donation) {
            throw new MessageException('Cannot change the amount of a fixed price item.');
        }

        $this->price = $amount;

        if ($this->is_recurring) {
            $this->recurring_amount = $amount;

            if (! $this->recurring_with_initial_charge) {
                $this->price = 0.00;
            }
        }

        $this->original_price = $this->price;
        $this->save();

        $this->order->calculate();
        $this->order->reapplyPromos();

        return $this;
    }

    /**
     * Send a notification to the member
     *
     * @param \Ds\Models\Email|string $email
     * @param array $params
     * @return bool
     */
    public function notify($email, $params = [])
    {
        // if a string was passed in
        if (is_string($email)) {
            $email = Email::where('is_active', 1)->where('type', $email)->first();
        }

        // bail if the email template doesn't exist
        if (! isset($email) || ! is_a($email, Email::class)) {
            return false;
        }

        return $email->send($email->to, $this->notifyParams($params));
    }

    public function notifyParams(array $params = []): array
    {
        $params['product_name'] = $this->variant->product->name;
        $params['product_code'] = $this->code;
        $params['variant_name'] = $this->variant->variantname;
        $params['price'] = money((($this->variant->saleprice != '' && $this->variant->saleprice != 0) ? $this->variant->saleprice : $this->variant->price), $this->order->currency_code)->format('$0,0.00 [$$$]');
        $params['original_price'] = money($this->variant->price, $this->order->currency_code)->format('$0,0.00 [$$$]');
        $params['weight'] = number_format($this->variant->weight, 2) . 'lbs';
        $params['quantity'] = $this->qty;
        $params['price_paid'] = money($this->price, $this->order->currency_code)->format('$0,0.00 [$$$]');
        $params['recurring_amount'] = money($this->recurring_amount + $this->dcc_recurring_amount, $this->order->currency_code)->format('$0,0.00 [$$$]');
        $params['gl_code'] = $this->gl_code;
        $params['designation'] = $this->designation;

        $check_in_url = secure_site_url(route('backend.orders.checkin', ['o' => $this->order->getKey(), 'i' => $this->getKey()], false));
        $params['checkin_qr'] = '<img src="https://chart.googleapis.com/chart?chs=500x500&cht=qr&chl=' . urlencode($check_in_url) . '" height="300" width="300" />';

        // fill in blanks for 50 custom fields
        foreach (range(0, 50) as $sequence) {
            $sequence_padded = str_pad((string) $sequence, 2, '0', STR_PAD_LEFT);
            $params['custom_field_' . $sequence_padded] = '';
            $params['custom_field_value_' . $sequence_padded] = '';
        }

        $params['download_links'] = '';

        if ($this->is_downloadable) {
            $params['download_links'] = sprintf(
                '%s - %s - Download: <a href="%s" target="_blank">%s</a>',
                $this->variant->product->name,
                $this->variant->variantname,
                $this->download_link,
                $this->download_link
            );
        }

        $this->fields->each(function ($field) use (&$params) {
            $sequence_padded = str_pad((string) $field->sequence, 2, '0', STR_PAD_LEFT);
            if (in_array($field->type, ['select', 'multi-select']) && $field->format == 'advanced') {
                $params['custom_field_' . $sequence_padded] = $field->value_formatted;
                $params['custom_field_value_' . $sequence_padded] = $field->value;
            } else {
                $params['custom_field_' . $sequence_padded] = $field->value;
                $params['custom_field_value_' . $sequence_padded] = $field->value;
            }
        });

        $params['tribute_name'] = $this->tribute->tributeType->label ?? '';
        $params['tribute_notification_type'] = $this->tribute->notify ?? '';
        $params['tribute_individual_name'] = $this->tribute->name ?? '';
        $params['tribute_recipient_name'] = $this->tribute->notify_name ?? '';
        $params['tribute_recipient_email'] = $this->tribute->notify_email ?? '';
        $params['tribute_recipient_mailing_address'] = $this->tribute ? address_format($this->tribute->notify_address, '', $this->tribute->notify_city, $this->tribute->notify_state, $this->tribute->notify_zip, $this->tribute->notify_country) : '';
        $params['tribute_tribute_message'] = $this->tribute->message ?? '';

        $params['membership_expiry_date'] = fromUtcFormat($this->groupAccount->groupAccountTimespan->end_date ?? null, 'F d, Y');
        $params['membership_name'] = $this->groupAccount->group->name ?? null;
        $params['membership_description'] = $this->groupAccount->group->description ?? null;
        $params['membership_renewal_url'] = $this->groupAccount->group->rewewal_url ?? null;

        return $params;
    }

    /**
     * Liquid representation of model.
     */
    public function toLiquid()
    {
        return \Ds\Domain\Theming\Liquid\Drop::factory($this, 'LineItem');
    }
}
