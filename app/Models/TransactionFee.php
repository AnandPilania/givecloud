<?php

namespace Ds\Models;

use Ds\Domain\Commerce\Currency;
use Ds\Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class TransactionFee extends Model
{
    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [
        'settlement_currency' => 'USD',
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        self::creating(function ($fee) {
            $fee->exchange_rate = Currency::getExchangeRate($fee->currency, $fee->settlement_currency);
            $fee->settlement_amount = $fee->amount * $fee->exchange_rate;
        });
    }

    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Attribute Mutator: Type
     *
     * @param string $value
     */
    public function setTypeAttribute($value)
    {
        if (preg_match('/^(payment|refund)$/i', $value)) {
            $value = strtolower($value);
        } else {
            $value = null;
        }

        $this->attributes['type'] = $value;
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
}
