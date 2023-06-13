<?php

namespace Ds\Models;

use Ds\Domain\Theming\Liquid\Liquidable;
use Ds\Eloquent\Hashids;
use Ds\Eloquent\SoftDeleteUserstamp;
use Ds\Illuminate\Database\Eloquent\Auditable;
use Ds\Illuminate\Database\Eloquent\Factories\HasFactory;
use Ds\Illuminate\Database\Eloquent\HasAuditing;
use Ds\Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductCustomField extends Model implements Auditable, Liquidable
{
    use HasAuditing;
    use Hashids;
    use HasFactory;
    use SoftDeletes;
    use SoftDeleteUserstamp;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'productfields';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'body' => 'string',
        'hint' => 'string',
        'isrequired' => 'boolean',
        'format' => 'string',
        'map_to_product_meta' => 'string',
        'name' => 'string',
        'options' => 'string',
        'default_value' => 'string',
        'sequence' => 'integer',
        'type' => 'string',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'choices',
    ];

    public function hasJsonOptions(): bool
    {
        return $this->format === 'advanced';
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'productid');
    }

    /**
     * Attribute Accessor: Choices
     *
     * @return array
     */
    public function getChoicesAttribute()
    {
        if (empty($this->options)) {
            return [];
        }

        if ($this->hasJsonOptions()) {
            return json_decode($this->options);
        }

        return array_map(function ($value) {
            return (object) [
                'label' => $value,
                'value' => $value,
            ];
        }, explode("\n", str_replace("\r\n", "\n", $this->options)));
    }

    /**
     * Attribute Mutator: Choices
     *
     * @param array|string $value
     */
    public function setChoicesAttribute($value)
    {
        if (is_array($value)) {
            if ($this->hasJsonOptions()) {
                $value = json_encode($value);
            } else {
                $value = implode("\n", $value);
            }
        }

        $this->attributes['options'] = $value;
    }

    /**
     * Attribute mask: value
     *
     * Uses the pivot relationship to access the value of this custom
     * field.
     *
     * @return mixed
     */
    public function getValueAttribute()
    {
        if ($this->pivot->value) {
            return $this->pivot->value;
        }

        return null;
    }

    /**
     * Attribute mask: value_formatted
     *
     * @return string
     */
    public function getValueFormattedAttribute()
    {
        if ($this->type == 'check') {
            if ($this->value == 1 || $this->value == 'Y') {
                return 'Yes';
            }

            return 'No';
        }

        if ($this->type === 'select') {
            foreach ($this->choices as $choice) {
                if ($choice->value === $this->value) {
                    return $choice->label;
                }
            }

            return $this->value;
        }

        if ($this->type === 'multi-select') {
            return collect(explode(',', $this->value))
                ->map(function ($value) {
                    return collect($this->choices)
                        ->firstWhere('value', $value)->label ?? $value;
                })->implode(', ');
        }

        return trim($this->value);
    }

    /**
     * Liquid representation of model.
     */
    public function toLiquid()
    {
        return \Ds\Domain\Theming\Liquid\Drop::factory($this, 'Field');
    }
}
