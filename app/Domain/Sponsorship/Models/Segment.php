<?php

namespace Ds\Domain\Sponsorship\Models;

use Ds\Domain\Theming\Liquid\Liquidable;
use Ds\Eloquent\SoftDeleteBooleans;
use Ds\Eloquent\Userstamps;
use Ds\Illuminate\Database\Eloquent\AuthoritativeDatabase;
use Ds\Illuminate\Database\Eloquent\Factories\HasFactory;
use Ds\Illuminate\Database\Eloquent\HasAuthoritativeDatabase;
use Ds\Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class Segment extends Model implements AuthoritativeDatabase, Liquidable
{
    use HasAuthoritativeDatabase;
    use HasFactory;
    use SoftDeleteBooleans;
    use Userstamps;

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_deleted' => 'boolean',
        'is_geographic' => 'boolean',
        'sequence' => 'integer',
        'show_as_filter' => 'boolean',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'value',
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        // always order account_types by sequence
        static::addGlobalScope('sequence', function (Builder $builder) {
            $builder->orderBy('sequence')->orderBy('id');
        });
    }

    /**
     * Relationship: Items
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items()
    {
        return $this->hasMany(SegmentItem::class);
    }

    /**
     * Attribute Accessor: Type Formatted
     *
     * @return string
     */
    public function getTypeFormattedAttribute()
    {
        if ($this->type === 'advanced-multi-select') {
            return 'Advanced Multi-Select';
        }

        return Str::title($this->type);
    }

    /**
     * Attribute mask: item
     *
     * This is added to quickly reference the selected item
     * (for multi-select segments).
     */
    public function getItemAttribute()
    {
        if (isset($this->pivot->segment_item_id)) {
            return $this->items->firstWhere('id', $this->pivot->segment_item_id);
        }
    }

    /**
     * Attribute mask: value
     *
     * This only works when used from the sponsorship model pivot relationship.
     *
     * @return string|null
     */
    public function getValueAttribute()
    {
        if (isset($this->pivot->value) && trim($this->pivot->value)) {
            return $this->pivot->value;
        }

        if (isset($this->pivot->segment_item_name) && trim($this->pivot->segment_item_name)) {
            return $this->pivot->segment_item_name;
        }
    }

    /**
     * Scope: Active segments
     *
     * @param \Illuminate\Database\Query\Builder $query
     */
    public function scopeActive($query)
    {
        return $query->where('is_deleted', false);
    }

    /**
     * Scope: Private segments
     *
     * @param \Illuminate\Database\Query\Builder $query
     */
    public function scopePrivateSegments($query)
    {
        return $query->where('show_in_detail', false);
    }

    /**
     * Scope: Public segments
     *
     * @param \Illuminate\Database\Query\Builder $query
     */
    public function scopePublicSegments($query)
    {
        return $query->where('show_in_detail', true);
    }

    /**
     * Scope: Filterable Segments
     *
     * @param \Illuminate\Database\Query\Builder $query
     */
    public function scopeFilterable($query)
    {
        return $query->where('show_as_filter', true);
    }

    /**
     * Liquid representation of model.
     */
    public function toLiquid()
    {
        return \Ds\Domain\Theming\Liquid\Drop::factory($this, 'SponseeField');
    }
}
