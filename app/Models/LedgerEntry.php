<?php

namespace Ds\Models;

use Ds\Domain\Sponsorship\Models\Sponsorship;
use Ds\Eloquent\SpammableScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LedgerEntry extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];

    protected $dates = ['captured_at'];

    protected $casts = [
        'amount' => 'double',
        'original_amount' => 'double',
        'qty' => 'integer',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class, 'item_id');
    }

    public function ledgerable(): MorphTo
    {
        return $this->morphTo()->withoutGlobalScope(SpammableScope::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class)
            ->withSpam()
            ->withTrashed();
    }

    public function sponsorship(): BelongsTo
    {
        return $this->belongsTo(Sponsorship::class);
    }

    public function supporter(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'supporter_id');
    }
}
