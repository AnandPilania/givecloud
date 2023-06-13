<?php

namespace Ds\Models;

use Ds\Illuminate\Database\Eloquent\Factories\HasFactory;
use Ds\Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Hook extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * List of allowed content_types.
     *
     * @var array
     */
    public const CONTENT_TYPES = ['application/json'];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'active' => 'boolean',
        'content_type' => 'string',
        'insecure_ssl' => 'boolean',
        'payload_url' => 'string',
        'secret' => 'string',
    ];

    public function deliveries(): HasMany
    {
        return $this->hasMany(HookDelivery::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(HookEvent::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('active', true);
    }
}
