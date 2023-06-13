<?php

namespace Ds\Models;

use Ds\Illuminate\Database\Eloquent\Factories\HasFactory;
use Ds\Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class UserDefinedField extends Model
{
    use HasFactory;

    public const ENTITIES = ['account'];

    public const FIELD_TYPES = ['choice', 'multiple_choice', 'radio', 'short_text'];

    protected $guarded = ['id'];

    protected $casts = [
        'entity' => 'string',
        'field_attributes' => 'json',
        'field_type' => 'string',
        'name' => 'string',
    ];

    public function getOptions(): array
    {
        return $this->field_attributes['options'] ?? [];
    }

    public function members(): MorphToMany
    {
        return $this->morphedByMany(Member::class, 'user_defined_fieldable')
            ->using(UserDefinedFieldValue::class)
            ->withPivot('value')
            ->withTimestamps();
    }
}
