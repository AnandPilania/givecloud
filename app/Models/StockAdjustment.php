<?php

namespace Ds\Models;

use Ds\Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAdjustment extends Model
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'quantity' => 'integer',
        'occurred_at' => 'datetime',
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        self::created(function ($adjustment) {
            if ($adjustment->type === 'physical_count') {
                $adjustment->variant->quantity = $adjustment->quantity;
                $adjustment->variant->last_physical_count_id = $adjustment->id;
            } else {
                if ($adjustment->state === 'sold') {
                    $adjustment->variant->quantity -= $adjustment->quantity;
                } else {
                    $adjustment->variant->quantity += $adjustment->quantity;
                }
            }

            $adjustment->variant->quantitymodifieddatetime = $adjustment->occurred_at->copy();
            $adjustment->variant->quantitymodifiedbyuserid = $adjustment->user_id;
            $adjustment->variant->save();
        });
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class)->withSpam();
    }

    public function refund(): BelongsTo
    {
        return $this->belongsTo(Refund::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(Variant::class)
            ->withTrashed();
    }
}
