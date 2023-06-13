<?php

namespace Ds\Models;

use Carbon\Carbon;
use Ds\Domain\Commerce\Gateways\PayPalExpressGateway;
use Ds\Domain\Commerce\Models\PaymentProvider;
use Ds\Domain\Commerce\Money;
use Ds\Domain\Commerce\Responses\TransactionResponse;
use Ds\Domain\Commerce\SourceTokenChargeOptions;
use Ds\Domain\Theming\Liquid\Liquidable;
use Ds\Eloquent\Hashids;
use Ds\Enums\RecurringPaymentProfileStatus;
use Ds\Illuminate\Database\Eloquent\Auditable;
use Ds\Illuminate\Database\Eloquent\Factories\HasFactory;
use Ds\Illuminate\Database\Eloquent\HasAuditing;
use Ds\Illuminate\Database\Eloquent\Model;
use Ds\Models\Observers\PaymentMethodObserver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentMethod extends Model implements Auditable, Liquidable
{
    use HasAuditing;
    use HasFactory;
    use Hashids;
    use SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'deleted_at',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'cc_expiry' => 'date',
        'use_as_default' => 'boolean',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'account_number',
        'fa_icon',
        'is_expired',
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        self::observe(new PaymentMethodObserver);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function paymentProvider(): BelongsTo
    {
        return $this->belongsTo(PaymentProvider::class);
    }

    public function recurringPaymentProfiles(): HasMany
    {
        return $this->hasMany(RecurringPaymentProfile::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * The masked account number.
     *
     * @return string
     */
    public function getAccountNumberAttribute()
    {
        if (data_get($this, 'paymentProvider.gateway') instanceof PayPalExpressGateway) {
            return $this->token;
        }

        $first = '*';

        if ($this->account_type === 'Visa') {
            $first = 4;
        }

        if ($this->account_type === 'MasterCard') {
            $first = 5;
        }

        if ($this->account_type === 'American Express') {
            $first = 3;
        }

        if ($this->account_type === 'Diners Club') {
            $first = 3;
        }

        if ($this->account_type === 'Discover') {
            $first = 6;
        }

        if ($this->account_type === 'JCB') {
            $first = 2;
        }

        return "{$first}*** **** **** {$this->account_last_four}";
    }

    /**
     * The masked account number.
     *
     * @return string
     */
    public function getLastFourOfAccountNumberAttribute()
    {
        return $this->attributes['account_last_four'];
    }

    public function getBillingNameAttribute(): ?string
    {
        return trim("{$this->billing_first_name} {$this->billing_last_name}") ?: null;
    }

    /**
     * Get is_expired attribute
     *
     * @return bool
     */
    public function getIsExpiredAttribute($value)
    {
        if ($this->cc_expiry) {
            return $this->cc_expiry->endOfMonth()->isPast();
        }

        return false;
    }

    /**
     * Scope: The default payment method.
     *
     * @param \Illuminate\Database\Query\Builder $query
     */
    public function scopeDefaultPaymentMethod($query)
    {
        $query->where('status', 'ACTIVE');
        $query->where('use_as_default', true);
    }

    /**
     * Scope: Active
     *
     * @param \Illuminate\Database\Query\Builder $query
     */
    public function scopeActive($query)
    {
        $query->where('status', 'ACTIVE');
    }

    /**
     * Scope: In Use (assigned to a non-cancelled profile)
     *
     * @param \Illuminate\Database\Query\Builder $query
     */
    public function scopeIsInUse($query)
    {
        $query->whereHas('recurringPaymentProfiles', function ($q) {
            $q->where('status', '!=', RecurringPaymentProfileStatus::CANCELLED);
        });
    }

    /**
     * Scope: Payment Methods that are useable (in good standing)
     *
     * @param \Illuminate\Database\Query\Builder $query
     */
    public function scopeGoodStanding($query)
    {
        $query->where(function ($query) {
            $query->whereNull('cc_expiry')
                ->orWhereDate('cc_expiry', '>=', toUtc('today'));
        });
    }

    /**
     * Scope: Payment Methods expiring in 30 days.
     *
     * @param \Illuminate\Database\Query\Builder $query
     */
    public function scopeExpiring($query): void
    {
        $query->whereNotNull('cc_expiry');

        // `cc_expiry` is always the last day of the month using addMonth below
        // means that for months with 31 days, like May, that are preceeded by a month
        // with less than 31 days with be skipped since (Apr 30)->addMonth = May 30
        // so instead we'll just use 30 days out
        $query->whereDate('cc_expiry', toUtc('today')->addDays(30));
    }

    public function scopeExpiringByEndOfNextMonth(Builder $query): void
    {
        $query
            ->whereNotNull('cc_expiry')
            ->whereDate('cc_expiry', '>=', toUtc('today'))
            ->whereDate('cc_expiry', '<=', toUtc('today')->addMonthWithoutOverflow()->endOfMonth());
    }

    public function scopeNotExpiringByEndNextMonth(Builder $query): void
    {
        $query->where(function ($query) {
            $query
                ->whereNull('cc_expiry')
                ->orWhereDate('cc_expiry', '>', toUtc('today')->addMonthWithoutOverflow()->endOfMonth());
        });
    }

    /**
     * Scope: Payment Methods expiring today.
     *
     * @param \Illuminate\Database\Query\Builder $query
     */
    public function scopeExpired($query)
    {
        $query->whereNotNull('cc_expiry');
        $query->whereDate('cc_expiry', toUtc('today'));
    }

    public function scopeIsExpired(Builder $query): void
    {
        $query
            ->whereNotNull('cc_expiry')
            ->whereDate('cc_expiry', '<=', toUtc('today'));
    }

    public function scopeValid(Builder $query): void
    {
        $query->active()->goodStanding();
    }

    /**
     * Set default payment method.
     *
     * @return void
     */
    public function useAsDefaultPaymentMethod()
    {
        $this->member->paymentMethods()->update(['use_as_default' => false]);
        $this->use_as_default = true;
        $this->save();

        // go over all recurring payments that are active or suspended and upated the payment method
        // THIS SHOULD BE UPDATED at some point to be conditional
        foreach ($this->member->recurringPaymentProfiles()->whereIn('status', [RecurringPaymentProfileStatus::ACTIVE, RecurringPaymentProfileStatus::SUSPENDED])->get() as $profile) {
            $profile->payment_method_id = $this->id;
            $profile->save();
        }
    }

    /**
     * Make a charge using the source token.
     *
     * @param float $amount
     * @param string $currencyCode
     * @param \Ds\Domain\Commerce\SourceTokenChargeOptions $options
     * @return \Ds\Domain\Commerce\Responses\TransactionResponse
     */
    public function charge($amount, $currencyCode = null, SourceTokenChargeOptions $options = null): TransactionResponse
    {
        $options ??= new SourceTokenChargeOptions;

        return $this->paymentProvider->chargeSourceToken($this, new Money($amount, $currencyCode), $options);
    }

    /**
     * Update the Customer Vault associated with Payment Method.
     *
     * @param \Ds\Domain\Commerce\Responses\TransactionResponse $res
     * @return void
     */
    public function updateWithTransactionResponse(TransactionResponse $res)
    {
        $provider = $res->getProvider();

        $this->status = 'ACTIVE';
        $this->payment_provider_id = $provider->id;
        $this->token = $res->getSourceToken();
        $this->token_type = $res->getTokenType();
        $this->fingerprint = $res->getFingerprint();

        if ($res->getCustomerRef()) {
            if ($provider->provider === 'authorizenet') {
                $this->member->authorizenet_customer_id = $res->getCustomerRef();
                $this->member->save();

                $this->authorizenet_customer_id = $res->getCustomerRef();
            }

            if ($provider->provider === 'paysafe') {
                $this->member->paysafe_profile_id = $res->getCustomerRef();
                $this->member->save();

                $this->paysafe_profile_id = $res->getCustomerRef();
            }

            if ($provider->provider === 'stripe') {
                $this->member->stripe_customer_id = $res->getCustomerRef();
                $this->member->save();

                $this->stripe_customer_id = $res->getCustomerRef();
            }

            if ($provider->provider === 'vanco') {
                $this->member->vanco_customer_ref = $res->getCustomerRef();
                $this->member->save();

                $this->vanco_customer_ref = $res->getCustomerRef();
            }

            if ($provider->provider === 'braintree') {
                $this->member->braintree_customer_id = $res->getCustomerRef();
                $this->member->save();

                $this->braintree_customer_id = $res->getCustomerRef();
            }
        }

        $this->account_type = $res->getAccountType();
        $this->cc_wallet = $res->getCardWallet();

        if ($res->getCardNumber() || $res->getAchAccount()) {
            $this->account_last_four = $res->getAccountLastFour();
        }

        if ($res->getCardExpiry()) {
            $this->cc_expiry = Carbon::createFromFormat('!my', $res->getCardExpiry())->endOfMonth();
        }

        if ($res->getAchAccount()) {
            $this->ach_bank_name = $res->getAchBank();
            $this->ach_entity_type = $res->getAchEntity();
            $this->ach_account_type = $res->getAchType();
            $this->ach_routing = $res->getAchRouting();
        }

        if (empty($this->display_name)) {
            $this->display_name = $this->account_type;
        }

        $this->save();

        $this->load('paymentProvider');
    }

    /**
     * Get fa_icon attribute
     *
     * @return string
     */
    public function getFaIconAttribute()
    {
        return fa_payment_icon($this->account_type);
    }

    /**
     * Liquid representation of model.
     */
    public function toLiquid()
    {
        return \Ds\Domain\Theming\Liquid\Drop::factory($this, 'PaymentMethod');
    }
}
