<?php

namespace Ds\Domain\FeaturePreviews\Models;

use Ds\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserStateActivity extends Model
{
    use HasFactory;

    protected $table = 'feature_preview_user_state_activities';

    protected $casts = [
        'changes' => 'json',
    ];

    protected $fillable = [
        'user_id',
        'changes',
        'feature',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
