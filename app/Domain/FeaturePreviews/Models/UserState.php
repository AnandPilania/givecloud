<?php

namespace Ds\Domain\FeaturePreviews\Models;

use Ds\Domain\FeaturePreviews\Concerns\HasActivities;
use Ds\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserState extends Model
{
    use HasFactory;
    use HasActivities;

    protected $table = 'feature_preview_user_states';

    protected $casts = [
        'enabled' => 'bool',
    ];

    protected $fillable = [
        'feature',
        'user_id',
        'enabled',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('enabled', true);
    }

    public function scopeForCurrentUser(Builder $query): Builder
    {
        return $query->where('user_id', user('id'));
    }
}
