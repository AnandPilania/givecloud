<?php

namespace Ds\Models;

use Ds\Domain\Shared\Exceptions\MessageException;
use Ds\Eloquent\Permissions;
use Ds\Eloquent\Userstamps;
use Ds\Illuminate\Database\Eloquent\Factories\HasFactory;
use Ds\Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PromoCode extends Model
{
    use HasFactory;
    use Permissions;
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
    protected $table = 'productpromocode';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'startdate',
        'enddate',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'startdate' => 'date',
        'enddate' => 'date',
        'discount' => 'double',
        'is_free_shipping' => 'boolean',
        'usage_count' => 'integer',
        'usage_limit' => 'integer',
        'usage_limit_per_account' => 'integer',
    ];

    /**
     * The attributes to append when serializing.
     *
     * @var array
     */
    protected $appends = [
        'is_limited_per_account',
    ];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            ProductCategory::class,
            'productpromocodecategory',
            'promocodeid',
            'categoryid',
            'id'
        );
    }

    public function memberships(): BelongsToMany
    {
        return $this->belongsToMany(Membership::class, 'membership_promocodes', 'promocode', 'membership_id', 'code', 'id');
    }

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'order_promocodes', 'promocode', 'order_id', 'code', 'id');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'productpromocodeproduct', 'promocodeid', 'productid', 'id');
    }

    /**
     * Scope: appliesToProduct - filter for codes that apply to specific
     * (by product id or the product's category)
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $product_id
     */
    public function scopeAppliesToProduct($query, $product_id)
    {
        $query->whereRaw(
            '(
                   (productpromocode.id in (select x1.promocodeid from productpromocodecategory as x1 inner join productcategorylink as x2 on x1.categoryid = x2.categoryid and x2.productid = ?))
                OR (productpromocode.id in (select x3.promocodeid from productpromocodeproduct as x3 where x3.productid = ?))
            )',
            [$product_id, $product_id]
        );
    }

    /**
     * Scope: notExpired - only codes that are not expired (startdate / enddate)
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeNotExpired($query)
    {
        $query->whereRaw(
            '(
                    (productpromocode.startdate IS NULL OR productpromocode.startdate <= ?)
                AND (productpromocode.enddate IS NULL OR productpromocode.enddate >= ?)
            )',
            [now(), now()]
        );
    }

    /**
     * Scope: limited per account
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeLimitedPerAccount($query)
    {
        $query->whereNotNull('usage_limit_per_account');
    }

    /**
     * Scope: limited per account
     *
     * @return bool
     */
    public function getIsLimitedPerAccountAttribute()
    {
        return isset($this->usage_limit_per_account) && $this->usage_limit_per_account > 0;
    }

    /**
     * Attribute Mask: is_valid
     *
     * @return bool
     */
    public function getIsValidAttribute()
    {
        return (! $this->startdate || ($this->startdate && $this->startdate->lte(now())))
            && (! $this->enddate || ($this->enddate && $this->enddate->gte(now())));
    }

    /**
     * Attribute Mask: code
     *
     * !!! WARNING !!!
     * If this attribute mask disappears, for some reason $promoCode->code STOPS WORKING!!
     *
     * @return string
     */
    public function getCodeAttribute($val)
    {
        return $val;
    }

    /**
     * Attribute Mask: discount_formatted
     * A nice human format for the discount.
     * Example:
     * "30% OFF, Free Shipping"
     *
     * @return string
     */
    public function getDiscountFormattedAttribute($val)
    {
        $discount_parts = [];

        // dollar/percent off
        if ($this->discount > 0) {
            if ($this->discount_type == 'percent' || $this->discount_type == 'bxgy_percent') {
                $discount_parts[] = number_format($this->discount, 2) . '% OFF';
            } else {
                $discount_parts[] = currency()->symbol . number_format($this->discount, 2) . ' OFF';
            }
        }

        // free shipping
        if ($this->is_free_shipping) {
            $discount_parts[] = ($this->discount_parts) ? $this->free_shipping_label : 'Free Shipping';
        }

        // return value
        return implode(', ', $discount_parts);
    }

    public function membershipsCount(): BelongsToMany
    {
        return $this->memberships()
            ->selectRaw('count(*) as aggregate')
            ->groupBy('promocode');
    }

    /**
     * Attribute Mask: memberships_count
     *
     * Solution stolen from:
     * http://stackoverflow.com/questions/25973095/getting-count-from-pivot-table-in-laravel-eloquent
     *
     * @return bool
     */
    public function getMembershipsCountAttribute()
    {
        if (! array_key_exists('membershipsCount', $this->relations)) {
            $this->load('membershipsCount');
        }
        $related = $this->getRelation('membershipsCount')->first();

        return ($related) ? $related->aggregate : 0;
    }

    /**
     * Apply the discount
     *
     * @param float $original_price the price we are discounting
     * @param \Ds\Domain\Commerce\Currency|string $currency_code
     * @return float
     */
    public function applyPromo($original_price, $currency_code = null)
    {
        // if dollar discount
        if ($this->discount_type === 'dollar') {
            // return the discounted price by dollars off
            return $original_price - money($this->discount)->toCurrency($currency_code)->getAmount();
        }

        // if percent discount
        if ($this->discount_type === 'percent') {
            // discount the price by percent
            return $original_price - ($original_price * ($this->discount / 100));
        }

        // return $original_price if the discount wasn't applied
        // (no idea why this would ever happen)
        return $original_price;
    }

    /**
     * Intelligently apply multiple promo codes to a price and
     * determine which promotion is the best.
     *
     * @param \Illuminate\Support\Collection|array $promos A collection of \Ds\Models\PromoCode
     * @param float $original_price the price being discounted
     * @param \Ds\Domain\Commerce\Currency|string $currency_code
     * @return \stdClass|bool Returns the chosen price and promocode as (object) [price=>0, promo=>'']. Returns FALSE if no promo can be applied (expired)
     */
    public static function applyPromos($promos, $original_price, $currency_code = null)
    {
        $prices = collect();

        // loop over each possible promo and collect each discount
        // so we can compare each
        foreach ($promos as $promo) {
            // if the promotion is valid
            if (! $promo->is_valid) {
                continue;
            }

            // application of BXGY promos are handled in the Order model
            if ($promo->discount_type === 'bxgy_dollar' || $promo->discount_type === 'bxgy_percent') {
                continue;
            }

            // apply the promotional price
            // - only if its lower than the original_price
            // - but never never less than 0
            $prices[] = (object) [
                'price' => (float) max(0, min($original_price, $promo->applyPromo($original_price, $currency_code))),
                'promo' => $promo->code,
                'promoCode' => $promo,
            ];
        }

        // if no prices were applied, return false
        if (count($prices) === 0) {
            return false;
        }

        return $prices->where('price', $prices->min('price'))->first();
        // return the best price
    }

    /**
     * Return all promocodes valid for a product
     *
     * @param int $product_id Product that you are searching for valid codes for. This will filter queries.
     * @param float $original_price The original price of the item. This is manually provided for performance.
     * @param \Illuminate\Support\Collection $promocodes Optionally limit the numebr of promo codes
     * @param \Ds\Domain\Commerce\Currency|string $currency_code
     * @return \stdClass|bool A list of promocodes that were applied
     */
    public static function discountByProduct($product_id, $original_price, Collection $promocodes, $currency_code = null)
    {
        if ($promocodes->count() === 0) {
            return false;
        }

        $codes = self::appliesToProduct($product_id)->notExpired()->get();

        $promocodes = $promocodes->filter(function ($promo) use ($codes) {
            return $codes->where('code', $promo->code)->count();
        });

        if ($promocodes->count() === 0) {
            return false;
        }

        return self::applyPromos($promocodes, $original_price, $currency_code);
    }

    /**
     * Tests the validity of a promocode and throws descriptive responses.
     *
     * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
     * !!!!!!!!!! POSSIBLE TO-DO !!!!!!!!!!!!!
     * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
     *
     * Simplify membership validation by using $order->member (instead of
     * member() which gets the logged in user. There is no logged in
     * user/donor in POS mode.)
     *
     * When a user logs in, set cart()->member_id = member()->id;
     * When setting a user in POS, $order->member_id is already being set.
     *
     * @param string $promo the promocode being validated
     * @param bool $is_pos Are we validating for a POS transaction? (default, NO - we aren't)
     * @param string $billing_email The email to validate against
     * @param \Ds\Models\Member $member The member to validate against
     * @return bool Returns TRUE if the promo is valid. Throws an error if it is not valid.
     */
    public static function validate($promo, $is_pos = false, $billing_email = null, $member = null)
    {
        // find the promo in the db (w/ relationships)
        if (is_a($promo, PromoCode::class)) {
            $promocode = (! $promo->relationLoaded('memberships')) ? $promo->load('memberships') : $promo;
            $promo = $promocode->code;
        } else {
            $promocode = PromoCode::whereCode($promo)->with('memberships')->first();
        }

        // if we didn't find it, the code doesn't exist
        if (! $promocode) {
            throw new MessageException('Code not found.');
        }

        // found it, but it expired
        if (! $promocode->is_valid) {
            throw new MessageException('Code has expired.');
        }

        // found it, but it's been used too many times already
        if ($promocode->usage_limit && $promocode->usage_count >= $promocode->usage_limit) {
            throw new MessageException('Code is no longer valid.');
        }

        // if the promo has a use limit PER PERSON
        // (we require either an email or a member to validate against)
        if ($promocode->usage_limit_per_account
            && ($billing_email || $member)
            && $promocode->countUseByAccount($billing_email, $member) >= $promocode->usage_limit_per_account) {
            throw new MessageException("Code can only be used ({$promocode->usage_limit_per_account}) per customer.");
        }

        // if this code is locked to a membership level
        if ($promocode->memberships->count() > 0) {
            // if there's no member
            if (! $member) {
                throw new MessageException('Code is only available to members.' . ((! $is_pos) ? ' Check to make sure you are logged in.' : ''));
            }

            // no membership or wrong membership level
            if (! $member->activeGroupPromocodes()->firstWhere('code', $promocode->code)) {
                throw new MessageException('Code is only available to active memberships.');
            }
        }

        // promocode is valid
        return true;
    }

    /**
     * Increment the usage count.
     */
    public function incrementUsageCount()
    {
        return $this->increment('usage_count');
    }

    /**
     * Decrement the usage count.
     */
    public function decrementUsageCount()
    {
        return $this->decrement('usage_count');
    }

    /**
     * Count the number of times this promo has been
     * used by a person.
     *
     * Orders will be identified by billingemail and member_id
     *
     * @param string $email
     * @param \Ds\Models\Member $member
     * @return int
     */
    public function countUseByAccount($email, $member = null)
    {
        // refering to all paid orders
        $orders = $this->orders()->paid();

        // if a member is provided, filter by member
        if ($member) {
            $orders->where('member_id', $member->id);

        // if no member provided, filter by email
        } else {
            $orders->where('billingemail', $email);
        }

        // return the resulting use count
        return $orders->count();
    }

    /**
     * Calcuate the usage count based on past orders.
     *
     * This should be used during the initial migration
     * but could also be used to refresh erroneous
     * values in the future.
     */
    public function calculateUsageCount()
    {
        $this->update(['usage_count' => $this->orders()->paid()->count(DB::raw('distinct invoicenumber'))]);
    }

    /**
     * Calcuate the usage count based on past orders
     * for all promocodes in the system.
     */
    public static function calculateAllUsageCounts()
    {
        self::all()->each(function ($p) {
            $p->calculateUsageCount();
        });
    }
}
