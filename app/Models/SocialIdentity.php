<?php

namespace Ds\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SocialIdentity extends Model
{
    use HasFactory;

    protected $casts = [
        'is_confirmed' => 'bool',
    ];

    protected $fillable = [
        'avatar',
        'is_confirmed',
        'provider_id',
        'provider_name',
    ];

    public function authenticatable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeOfProvider(Builder $query, string $provider): Builder
    {
        return $query->where('provider_name', $provider);
    }

    public function scopeConfirmed(Builder $query): Builder
    {
        return $query->where('is_confirmed', true);
    }
}
