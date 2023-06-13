<?php

namespace Ds\Models;

use Ds\Domain\Theming\Liquid\Drop;
use Ds\Domain\Theming\Liquid\Liquidable;
use Ds\Eloquent\Hashids;
use Ds\Eloquent\HasMetadata;
use Ds\Eloquent\Metadatable;
use Ds\Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

abstract class AbstractGroupAccount extends Pivot implements Liquidable, Metadatable
{
    use Hashids;
    use HasMetadata;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'is_active',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Membership::class)
            ->withTrashed();
    }

    /**
     * Mutator: is_active
     *
     * @return bool
     */
    public function getIsActiveAttribute()
    {
        $today = fromLocal('today');

        return ($this->start_date ?? $today)->lte($today)
            && ($this->end_date ?? $today)->gte($today);
    }

    /**
     * Attribute Accessor: Is Expired
     */
    public function getIsExpiredAttribute(): bool
    {
        if ($this->is_active) {
            return false;
        }

        if ($this->start_date && $this->start_date->isFuture()) {
            return false;
        }

        return true;
    }

    /**
     * Mutator: days_left
     *
     * @return int
     */
    public function getDaysLeftAttribute()
    {
        return ($this->end_date && $this->end_date->isFuture()) ? $this->end_date->diffInDays() : 0;
    }

    /**
     * Scope: active
     */
    public function scopeActive($builder)
    {
        $builder->where(function ($q) {
            $q->whereNull('start_date')
                ->orWhere('start_date', '<=', fromLocal('today'));
        })->where(function ($q) {
            $q->whereNull('end_date')
                ->orWhere('end_date', '>=', fromLocal('today'));
        });
    }

    /**
     * Scope: inactive
     */
    public function scopeInactive($builder)
    {
        $builder->where(function ($q) {
            $q->whereNotNull('start_date')
                ->where('start_date', '>', fromLocal('today'));
        })->orWhere(function ($q) {
            $q->whereNotNull('end_date')
                ->where('end_date', '<', fromLocal('today'));
        });
    }

    /**
     * Scope: Badges ()
     */
    public function scopeBadges($builder)
    {
        $builder->whereIn('group_id', function ($query) {
            $query->select('id')
                ->from(Membership::table())
                ->where('should_display_badge', 1);
        });
    }

    /**
     * Get a unique list of sources.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getSources()
    {
        return self::select('source')
            ->distinct()
            ->get()
            ->pluck('source');
    }

    /**
     * Get a unique list of end_reasons.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getEndReasons()
    {
        return self::select('end_reason')
            ->distinct()
            ->get()
            ->pluck('end_reason');
    }

    /**
     * Liquid representation of model.
     */
    public function toLiquid()
    {
        return Drop::factory($this, 'GroupAccount');
    }
}
