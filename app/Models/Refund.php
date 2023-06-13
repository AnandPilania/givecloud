<?php

namespace Ds\Models;

use Ds\Domain\Theming\Liquid\Liquidable;
use Ds\Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Refund extends Model implements Liquidable
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'payment_id' => 'integer',
        'amount' => 'double',
        'refunded_by_id' => 'integer',
        'created_at' => 'datetime:Y-m-d\TH:i:s\Z',
        'updated_at' => 'datetime:Y-m-d\TH:i:s\Z',
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        self::saved(function ($refund) {
            $refund->payment->onRefundSaved();
        });
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class)->withSpam();
    }

    public function refundedBy(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactionFee(): MorphOne
    {
        return $this->morphOne(TransactionFee::class, 'source');
    }

    /**
     * Scope: Succeeded or Pending
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeSucceededOrPending(Builder $query)
    {
        $query->whereIn('status', ['succeeded', 'pending']);
    }

    /**
     * Attribute Mutator: Status
     *
     * @param string $value
     */
    public function setStatusAttribute($value)
    {
        if (preg_match('/^(succeeded|pending|failed|canceled)$/i', $value)) {
            $value = strtolower($value);
        } else {
            $value = null;
        }

        $this->attributes['status'] = $value;
    }

    /**
     * Attribute Mutator: Currency
     *
     * @param string $value
     */
    public function setCurrencyAttribute($value)
    {
        $this->attributes['currency'] = $value ? strtoupper($value) : null;
    }

    /**
     * Attribute Mutator: Reason
     *
     * @param string $value
     */
    public function setReasonAttribute($value)
    {
        if (preg_match('/^(duplicate|fraudulent|requested_by_customer)$/i', $value)) {
            $value = strtolower($value);
        } else {
            $value = null;
        }

        $this->attributes['reason'] = $value;
    }

    /**
     * Attribute Mutator: Failure Reason
     *
     * @param string $value
     */
    public function setFailureReasonAttribute($value)
    {
        if (preg_match('/^(lost_or_stolen_card|expired_or_canceled_card|unknown)$/i', $value)) {
            $value = strtolower($value);
        } else {
            $value = null;
        }

        $this->attributes['failure_reason'] = $value;
    }

    /**
     * Liquid representation of model.
     */
    public function toLiquid()
    {
        return \Ds\Domain\Theming\Liquid\Drop::factory($this, 'Refund');
    }
}
