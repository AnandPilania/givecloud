<?php

namespace Ds\Models;

use Ds\Enums\ExternalReference\ExternalReferenceService;
use Ds\Enums\ExternalReference\ExternalReferenceType;
use Ds\Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ExternalReference extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'referenceable_id',
        'referenceable_type',
        'service',
        'type',
    ];

    public function referenceable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeSalesforce(Builder $query): Builder
    {
        return $query->where('service', ExternalReferenceService::SALESFORCE);
    }

    public function scopeSupporter(Builder $query): Builder
    {
        return $query->where('type', ExternalReferenceType::SUPPORTER);
    }

    public function scopeOrder(Builder $query): Builder
    {
        return $query->where('type', ExternalReferenceType::ORDER);
    }
}
