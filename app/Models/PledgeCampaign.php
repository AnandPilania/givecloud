<?php

namespace Ds\Models;

use Ds\Domain\Theming\Liquid\Liquidable;
use Ds\Eloquent\SoftDeleteUserstamp;
use Ds\Eloquent\Userstamps;
use Ds\Illuminate\Database\Eloquent\Factories\HasFactory;
use Ds\Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class PledgeCampaign extends Model implements Liquidable
{
    use HasFactory;
    use SoftDeletes;
    use SoftDeleteUserstamp;
    use Userstamps;

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'end_date' => 'date',
        'first_donation_date' => 'date',
        'funded_amount' => 'float',
        'funded_percent' => 'float',
        'last_donation_date' => 'date',
        'start_date' => 'date',
        'total_amount' => 'float',
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        self::saved(function ($campaign) {
            $campaign->calculate();
        });
    }

    public function pledges(): HasMany
    {
        return $this->hasMany(Pledge::class);
    }

    public function products(): MorphToMany
    {
        return $this->morphedByMany(Product::class, 'pledgable');
    }

    public function scopeActive(Builder $query): void
    {
        $query->where(function ($query) {
            $query->whereNull('start_date');
            $query->orWhereDate('start_date', '<=', now());
        });

        $query->where(function ($query) {
            $query->whereNull('end_date');
            $query->orWhereDate('end_date', '>=', now());
        });
    }

    /**
     * Query: Order Items
     *
     * @return \Illuminate\Database\Eloquent\Builder $query
     */
    public function orderItems()
    {
        // collect all order items
        $query = OrderItem::query()
            ->join('productinventory as _iv', '_iv.id', '=', 'productorderitem.productinventoryid')
            ->join('pledgables as _pl', function ($join) {
                $join->on('_pl.pledgable_id', '=', '_iv.productid')
                    ->where('_pl.pledgable_type', '=', 'product')
                    ->where('_pl.pledge_campaign_id', '=', $this->id);
            })->join('productorder', function ($join) {
                $join->on('productorder.id', '=', 'productorderitem.productorderid')
                    ->whereNull('productorder.deleted_at')
                    ->whereNotNull('productorder.confirmationdatetime');

                // date-scoped orders
                if ($this->start_date && $this->end_date) {
                    $join->whereBetween('productorder.confirmationdatetime', [$this->start_date, $this->end_date]);
                } elseif ($this->start_date) {
                    $join->where('productorder.confirmationdatetime', '>=', $this->start_date);
                } elseif ($this->end_date) {
                    $join->where('productorder.confirmationdatetime', '<=', $this->end_date);
                }
            });

        // return query
        return $query;
    }

    /**
     * Query: Transactions
     *
     * @return \Illuminate\Database\Eloquent\Builder $query
     */
    public function transactions()
    {
        $query = Transaction::query()
            ->join('recurring_payment_profiles', 'recurring_payment_profiles.id', '=', 'transactions.recurring_payment_profile_id')
            ->join('productorderitem', 'productorderitem.id', '=', 'recurring_payment_profiles.productorderitem_id')
            ->join('productinventory as _iv', '_iv.id', '=', 'productorderitem.productinventoryid')
            ->join('pledgables as _pl', function ($join) {
                $join->on('_pl.pledgable_id', '=', '_iv.productid')
                    ->where('_pl.pledgable_type', '=', 'product')
                    ->where('_pl.pledge_campaign_id', '=', $this->id);
            });

        // date-scoped orders
        if ($this->start_date && $this->end_date) {
            $query->whereBetween('transactions.order_time', [$this->start_date, $this->end_date]);
        } elseif ($this->start_date) {
            $query->where('transactions.order_time', '>=', $this->start_date);
        } elseif ($this->end_date) {
            $query->where('transactions.order_time', '<=', $this->end_date);
        }

        return $query;
    }

    /**
     * Calculate
     *
     * @return void
     */
    public function calculate($force_calculate = false)
    {
        // determine whether or not to calculate
        if (! $force_calculate) {
            if ($this->start_date && $this->start_date->isFuture()) {
                return;
            }

            if ($this->end_date && $this->end_date->isPast()) {
                return;
            }
        }

        // calculate aggregates
        $aggregates = $this->pledges()
            ->select([
                DB::raw('count(id) as total_count'),
                DB::raw('sum(functional_total_amount) as total_amount'),
                DB::raw('sum(functional_funded_amount) as funded_amount'),
                DB::raw('sum(funded_count) as funded_count'),
                DB::raw('min(first_donation_date) as first_donation_date'),
                DB::raw('max(last_donation_date) as last_donation_date'),
            ])->get()
            ->first();

        // populate aggregates
        $this->total_count = $aggregates->total_count;
        $this->total_amount = $aggregates->total_amount;
        $this->funded_amount = $aggregates->funded_amount;
        $this->funded_count = $aggregates->funded_count;
        $this->funded_percent = $this->total_amount ? $this->funded_amount / $this->total_amount : 1.0;
        $this->first_donation_date = $aggregates->first_donation_date;
        $this->last_donation_date = $aggregates->last_donation_date;

        if (empty($this->funded_percent)) {
            $this->funded_status = 'unfunded';
        } elseif ($this->funded_percent > 1.0) {
            $this->funded_status = 'overfunded';
        } elseif ($this->funded_percent < 1.0) {
            $this->funded_status = 'underfunded';
        } else {
            $this->funded_status = 'funded';
        }

        // save (but don't trigger observer)
        self::where('id', $this->id)
            ->update([
                'total_amount' => $this->total_amount,
                'total_count' => $this->total_count,
                'funded_amount' => $this->funded_amount,
                'funded_count' => $this->funded_count,
                'funded_percent' => $this->funded_percent,
                'funded_status' => $this->funded_status,
                'first_donation_date' => $this->first_donation_date,
                'last_donation_date' => $this->last_donation_date,
            ]);
    }

    /**
     * Calculate all
     *
     * @return void
     */
    public function calculatePledges()
    {
        $this->pledges()->chunk(200, function ($pledges) {
            $pledges->each(function ($pledge) {
                $pledge->calculate();
            });
        });
    }

    /**
     * Calculate all
     *
     * @return void
     */
    public static function calculateAll()
    {
        self::chunk(200, function ($campaigns) {
            $campaigns->each(function ($campaign) {
                $campaign->calculate();
            });
        });
    }

    /**
     * Liquid representation of model.
     */
    public function toLiquid()
    {
        return \Ds\Domain\Theming\Liquid\Drop::factory($this, 'PledgeCampaign');
    }
}
