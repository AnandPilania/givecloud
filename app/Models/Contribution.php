<?php

namespace Ds\Models;

use Ds\Eloquent\Hashids;
use Ds\Eloquent\SoftDeleteUserstamp;
use Ds\Eloquent\Spammable;
use Ds\Eloquent\Userstamps;
use Ds\Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Contribution extends Model
{
    use Hashids;
    use SoftDeletes;
    use SoftDeleteUserstamp;
    use Spammable;
    use Userstamps;

    public function order(): HasOne
    {
        return $this->hasOne(Order::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function supporter(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'supporter_id');
    }

    public function scopeWithWarnings(Builder $query): void
    {
        $query->where(function (Builder $query) {
            $query->where(function (Builder $query) {
                $query->where('ip_country_matches', false)
                    ->whereNotIn('payment_type', ['paypal', 'wallet_pay'])
                    ->whereNotIn('source', ['Import', 'Kiosk'])
                    ->whereNotIn(DB::raw('COALESCE(payment_card_wallet, "")'), ['apple_pay', 'google_pay'])
                    ->where('is_pos', false);
            })->orWhere('payment_card_cvc_check', 'failed');
        });
    }

    public function scopeComplete(Builder $query): Builder
    {
        if (sys_get('use_fulfillment') === 'never') {
            return $query;
        }

        if (sys_get('use_fulfillment') === 'shipping') {
            return $query->where(function (Builder $query) {
                $query->where('is_fulfilled', true)
                    ->where('shippable_items', '>', 0);
            })->orWhere('shippable_items', 0);
        }

        return $query->where('is_fulfilled', true);
    }

    public function scopeIncomplete(Builder $query): Builder
    {
        if (sys_get('use_fulfillment') === 'never') {
            return $query;
        }

        if (sys_get('use_fulfillment') === 'shipping') {
            return $query->where('is_fulfilled', false)
                ->where('shippable_items', '>', 0);
        }

        return $query->where('is_fulfilled', false);
    }

    public function scopeUnsynced(Builder $query): void
    {
        if (! dpo_is_enabled()) {
            return;
        }

        $query->where('payment_status', 'succeeded')
            ->where('dpo_auto_sync', true)
            ->where('is_dpo_synced', false);
    }

    public function getIsRefundedAttribute(): bool
    {
        return $this->total_refunded > 0 && $this->total_refunded === $this->total;
    }

    public function getIsPartiallyRefundedAttribute(): bool
    {
        return $this->total_refunded > 0 && $this->total_refunded < $this->total;
    }

    public function getHasWarningsAttribute(): bool
    {
        if (! $this->order) {
            return false;
        }

        return $this->order->warning_count > 0;
    }

    public function getIsFulfillableAttribute(): bool
    {
        if (! $this->order) {
            return false;
        }

        return $this->order->is_fulfillable;
    }

    public function getDccAmountAttribute(): ?float
    {
        return $this->order->dcc_total_amount
            ?? $this->transactions->sum('dcc_amount');
    }

    public function getIsUnsyncedAttribute(): bool
    {
        if (! dpo_is_enabled()) {
            return false;
        }

        if ($this->order) {
            return $this->order->is_unsynced;
        }

        return $this->transactions->filter->is_unsynced->isNotEmpty();
    }
}
