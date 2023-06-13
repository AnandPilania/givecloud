<?php

namespace Ds\Models;

use Ds\Domain\Commerce\ACH;
use Ds\Domain\Commerce\Currency;
use Ds\Domain\Commerce\Models\PaymentProvider;
use Ds\Domain\Theming\Liquid\Liquidable;
use Ds\Eloquent\Hashids;
use Ds\Eloquent\Spammable;
use Ds\Eloquent\Userstamps;
use Ds\Enums\CardBrand;
use Ds\Enums\CardType;
use Ds\Enums\PaymentType;
use Ds\Illuminate\Database\Eloquent\Factories\HasFactory;
use Ds\Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Str;
use Throwable;

class Payment extends Model implements Liquidable
{
    use HasFactory;
    use Hashids;
    use Spammable;
    use Userstamps;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'livemode' => 'boolean',
        'amount' => 'double',
        'amount_refunded' => 'double',
        'functional_exchange_rate' => 'double',
        'functional_total' => 'double',
        'paid' => 'boolean',
        'captured' => 'boolean',
        'captured_at' => 'datetime:Y-m-d\TH:i:s\Z',
        'refunded' => 'boolean',
        'source_account_id' => 'integer',
        'source_payment_method_id' => 'integer',
        'card_exp_month' => 'integer',
        'card_exp_year' => 'integer',
        'cheque_date' => 'date',
        'application_fee_billing' => 'boolean',
        'application_fee_amount' => 'double',
        'stripe_fee_amount' => 'double',
        'stripe_fee_exchange_rate' => 'double',
        'created_at' => 'datetime:Y-m-d\TH:i:s\Z',
        'updated_at' => 'datetime:Y-m-d\TH:i:s\Z',
    ];

    /** @var \Ds\Domain\Commerce\Models\PaymentProvider|null */
    private $paymentProvider = null;

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'source_account_id');
    }

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'payments_pivot', 'payment_id', 'order_id')->withSpam();
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'source_payment_method_id');
    }

    public function recurringPaymentProfiles(): BelongsToMany
    {
        return $this->belongsToMany(
            RecurringPaymentProfile::class,
            'payments_pivot',
            'payment_id',
            'recurring_payment_profile_id'
        );
    }

    public function getPaymentProviderAttribute(): ?PaymentProvider
    {
        return $this->paymentProvider ??= PaymentProvider::provider($this->gateway_type)->first();
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    public function successfulRefunds(): HasMany
    {
        return $this->refunds()
            ->succeededOrPending();
    }

    public function transactionFee(): MorphOne
    {
        return $this->morphOne(TransactionFee::class, 'source');
    }

    public function transactions(): BelongsToMany
    {
        return $this->belongsToMany(Transaction::class, 'payments_pivot', 'payment_id', 'transaction_id');
    }

    public function syncPaymentStatus(): void
    {
        if (optional($this->payment_provider)->supports('syncable_payment_status')) {
            $this->payment_provider->syncPaymentStatus($this);
        }
    }

    /**
     * Attribute Mutator: Type
     *
     * @param string $value
     */
    public function setTypeAttribute($value)
    {
        $value = strtolower($value);

        $this->attributes['type'] = in_array($value, PaymentType::all(), true) ? $value : null;
    }

    /**
     * Update the Payment with data from the Refund.
     *
     * @param bool $save
     */
    public function onRefundSaved(bool $save = true)
    {
        $this->amount_refunded = $this->refunds()->succeededOrPending()->sum('amount');
        $this->refunded = ($this->amount <= $this->amount_refunded);

        if ($save) {
            $this->save();
        }
    }

    /**
     * Attribute Mutator: Status
     *
     * @param string $value
     */
    public function setStatusAttribute($value)
    {
        if (preg_match('/^(succeeded|pending|failed)$/i', $value)) {
            $value = strtolower($value);
        } else {
            $value = null;
        }

        $this->attributes['status'] = $value;
    }

    /**
     * Attribute Mutator: Currency
     *
     * @param mixed $value
     */
    public function setCurrencyAttribute($value)
    {
        $this->attributes['currency'] = (string) new Currency($value);

        $this->functional_currency_code = sys_get('dpo_currency');
        $this->functional_exchange_rate = Currency::getExchangeRate($this->currency, $this->functional_currency_code);

        if (empty($this->functional_total)) {
            $this->functional_total = $this->amount * $this->functional_exchange_rate;
        }
    }

    /**
     * Attribute Mutator: Amount
     *
     * @param mixed $value
     */
    public function setAmountAttribute($value)
    {
        $this->attributes['amount'] = $value;

        $this->functional_total = $value * $this->functional_exchange_rate;
    }

    /**
     * Attribute Mutator: Outcome
     *
     * @param string $value
     */
    public function setOutcomeAttribute($value)
    {
        if (preg_match('/^(authorized|manual_review|issuer_declined|blocked|invalid)$/i', $value)) {
            $value = strtolower($value);
        } else {
            $value = null;
        }

        $this->attributes['outcome'] = $value;
    }

    /**
     * Attribute Mutator: Card Funding
     *
     * @param string $value
     */
    public function setCardFundingAttribute($value)
    {
        $value = strtolower($value);

        $this->attributes['card_funding'] = in_array($value, CardType::all(), true) ? $value : null;
    }

    /**
     * Attribute Mutator: Card Brand
     *
     * @param string $value
     */
    public function setCardBrandAttribute($value)
    {
        $value = preg_replace('/[^a-z]/', '', strtolower($value));

        $this->attributes['card_brand'] = empty($value) ? null : (CardBrand::labels()[$value] ?? CardBrand::labels()[null]);
    }

    /**
     * Attribute Mutator: Card Exp Month
     *
     * @param string $value
     */
    public function setCardExpMonthAttribute($value)
    {
        $value = (int) $value ?: null;

        $this->attributes['card_exp_month'] = $value;
    }

    /**
     * Attribute Mutator: Card Exp Year
     *
     * @param string $value
     */
    public function setCardExpYearAttribute($value)
    {
        $value = (int) $value ?: null;

        if (strlen($value) === 1) {
            $value = "200$value";
        } elseif (strlen($value) === 2) {
            $value = "20$value";
        }

        $this->attributes['card_exp_year'] = $value;
    }

    /**
     * Attribute Mutator: Card CVC Check
     *
     * @param string $value
     */
    public function setCardCvcCheckAttribute($value)
    {
        if (preg_match('/^(pass|fail|unavailable|unchecked)$/i', $value)) {
            $value = strtolower($value);
        } else {
            $value = null;
        }

        $this->attributes['card_cvc_check'] = $value;
    }

    /**
     * Attribute Mutator: Card Tokenization Method
     *
     * @param string $value
     */
    public function setCardTokenizationMethodAttribute($value)
    {
        if (preg_match('/^(apple_pay|android_pay)$/i', $value)) {
            $value = strtolower($value);
        } else {
            $value = null;
        }

        $this->attributes['card_tokenization_method'] = $value;
    }

    /**
     * Attribute Mutator: Card Address Line 1 Check
     *
     * @param string $value
     */
    public function setCardAddressLine1CheckAttribute($value)
    {
        if (preg_match('/^(pass|fail|unavailable|unchecked)$/i', $value)) {
            $value = strtolower($value);
        } else {
            $value = null;
        }

        $this->attributes['card_address_line1_check'] = $value;
    }

    /**
     * Attribute Mutator: Card Address Zip Check
     *
     * @param string $value
     */
    public function setCardAddressZipCheckAttribute($value)
    {
        if (preg_match('/^(pass|fail|unavailable|unchecked)$/i', $value)) {
            $value = strtolower($value);
        } else {
            $value = null;
        }

        $this->attributes['card_address_zip_check'] = $value;
    }

    /**
     * Attribute Mutator: Bank Account Holder Type
     *
     * @param string $value
     */
    public function setBankAccountHolderTypeAttribute($value)
    {
        $value = strtolower($value);

        if (empty($value)) {
            $value = null;
        } elseif (preg_match('/(business|company)/i', $value)) {
            $value = 'company';
        } else {
            $value = 'individual';
        }

        $this->attributes['bank_account_holder_type'] = $value;
    }

    /**
     * Attribute Mutator: Bank Routing Number
     *
     * @param string $value
     */
    public function setBankRoutingNumberAttribute($value)
    {
        $this->attributes['bank_routing_number'] = $value;

        if (empty($this->bank_name)) {
            $this->bank_name = ACH::getBankName($value);
        }
    }

    /**
     * Get type of the payment source.
     *
     * @return string
     */
    public function getSourceTypeAttribute(): string
    {
        if ($this->type === 'card') {
            return (string) ($this->card_brand ?: 'Credit Card');
        }

        if ($this->type === 'bank') {
            if ($this->currency === 'USD') {
                return 'ACH';
            }

            if ($this->currency === 'CAD') {
                return 'EFT';
            }

            return 'Bank';
        }

        if ($this->type === 'paypal') {
            return 'PayPal';
        }

        return ucwords($this->type);
    }

    /**
     * Get description of the payment source.
     *
     * @return string
     */
    public function getSourceDescriptionAttribute(): string
    {
        if ($this->type === 'card') {
            return "{$this->card_brand} ************{$this->card_last4}";
        }

        if ($this->type === 'bank') {
            return sprintf('%s %s ********%s', ucwords($this->bank_account_holder_type), ucwords($this->bank_account_type), $this->bank_last4);
        }

        if ($this->type === 'paypal') {
            if ($this->gateway_source) {
                return "PayPal {$this->gateway_source}";
            }

            return 'PayPal';
        }

        if ($this->type === 'cheque') {
            return "Cheque #{$this->cheque_number}";
        }

        return ucwords($this->type);
    }

    /**
     * Attribute Mutator: Signature
     *
     * @param string $value
     */
    public function setSignatureAttribute($value)
    {
        $value = Str::after($value, 'data:image/svg+xml;base64,');
        $value = base64_decode($value);

        $this->attributes['signature'] = $value;
    }

    /**
     * Attribute Mutator: Ip Address
     *
     * @param string $value
     */
    public function setIpAddressAttribute($value)
    {
        $this->attributes['ip_address'] = $value;

        try {
            $data = app('geoip')->getLocationData($value);

            $this->attributes['ip_country'] = data_get($data, 'iso_code');
        } catch (Throwable $e) {
            $this->attributes['ip_country'] = null;
        }
    }

    /**
     * Get description of the payment source.
     *
     * @return array
     */
    public function getVerificationMessagesAttribute(): array
    {
        $messages = [];

        if (! in_array($this->card_cvc_check, ['pass', 'fail'])
            && ! in_array($this->card_address_line1_check, ['pass', 'fail'])
            && ! in_array($this->card_address_zip_check, ['pass', 'fail'])) {
            return ['Not Available'];
        }

        if ($this->card_cvc_check === 'fail') {
            $messages[] = 'Bad CVC';
        }

        if ($this->card_address_line1_check === 'fail') {
            $messages[] = 'Bad Address';
        }

        if ($this->card_address_zip_check === 'fail') {
            $messages[] = 'Bad ZIP';
        }

        return $messages;
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', 'failed');
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

    public function getIsSpamColumn(): string
    {
        return 'spam';
    }

    /**
     * Get a fresh timestamp for the model.
     *
     * @return int|null
     */
    public function _freshUserstamp()
    {
        return user('id');
    }

    /**
     * Liquid representation of model.
     */
    public function toLiquid()
    {
        return \Ds\Domain\Theming\Liquid\Drop::factory($this, 'Payment');
    }
}
