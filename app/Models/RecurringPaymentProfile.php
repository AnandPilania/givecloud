<?php

namespace Ds\Models;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Ds\Domain\Commerce\Money;
use Ds\Domain\Shared\Date;
use Ds\Domain\Shared\Exceptions\MessageException;
use Ds\Domain\Sponsorship\Models\Sponsor;
use Ds\Domain\Sponsorship\Models\Sponsorship;
use Ds\Domain\Theming\Liquid\Liquidable;
use Ds\Eloquent\Hashids;
use Ds\Eloquent\HasMetadata;
use Ds\Eloquent\Metadatable;
use Ds\Enums\BillingPeriod;
use Ds\Enums\RecurringPaymentProfileStatus;
use Ds\Illuminate\Database\Eloquent\Auditable;
use Ds\Illuminate\Database\Eloquent\Factories\HasFactory;
use Ds\Illuminate\Database\Eloquent\HasAuditing;
use Ds\Illuminate\Database\Eloquent\Model;
use Ds\Models\Observers\RecurringPaymentProfileObserver;
use Ds\Repositories\TransactionRepository;
use Ds\Services\RecurringPaymentProfileService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RecurringPaymentProfile extends Model implements Auditable, Liquidable, Metadatable
{
    use HasAuditing;
    use HasFactory;
    use HasMetadata;
    use Hashids;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [
        'aggregate_amount' => 0.00,
        'max_failed_payments' => 1,
        'auto_bill_out_amt' => 0,
        'nsf_fee' => 0.00,
        'transaction_type' => 'Standard',
        'billing_period' => 'Month',
        'billing_frequency' => 12,
        'total_billing_cycles' => 0,
        'currency_code' => 'USD',
        'shipping_amt' => 0.00,
        'tax_amt' => 0.00,
        'init_amt' => 0.00,
        'num_cycles_completed' => 0,
        'outstanding_balance' => 0.00,
        'failed_payment_count' => 0,
        'payment_mutex' => 0,
        'dcc_enabled_by_customer' => 0,
        'dcc_amount' => 0.00,
        'dcc_per_order_amount' => 0.00,
        'dcc_rate' => 0.00,
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'last_payment_date',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'profile_start_date' => 'date',
        'final_payment_due_date' => 'date',
        'aggregate_amount' => 'double',
        'max_failed_payments' => 'integer',
        'auto_bill_out_amt' => 'boolean',
        'nsf_fee' => 'double',
        'billing_frequency' => 'integer',
        'billing_cycle_anchor' => 'date',
        'total_billing_cycles' => 'integer',
        'amt' => 'double',
        'shipping_amt' => 'double',
        'tax_amt' => 'double',
        'init_amt' => 'double',
        'next_billing_date' => 'date',
        'next_attempt_date' => 'date',
        'num_cycles_completed' => 'integer',
        'num_cycles_remaining' => 'integer',
        'outstanding_balance' => 'double',
        'failed_payment_count' => 'integer',
        'last_payment_amt' => 'double',
        'payment_mutex' => 'boolean',
        'is_manual' => 'boolean',
        'dcc_amount' => 'double',
        'dcc_enabled_by_customer' => 'boolean',
        'dcc_per_order_amount' => 'double',
        'dcc_rate' => 'double',
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        static::created(function ($model) {
            // save the profile has been created persist
            // the hash id to the database
            $model->profile_id = $model->hash_id;
            $model->save();
        });

        self::observe(new RecurringPaymentProfileObserver);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class)
            ->withTrashed();
    }

    public function product(): BelongsTo
    {
        // be sure we grab the related product, even if it's been deleted
        return $this->belongsTo(Product::class)
            ->withTrashed();
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'productorder_id')
            ->withSpam()
            ->withTrashed();
    }

    public function order_item(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class, 'productorderitem_id');
    }

    public function payments(): BelongsToMany
    {
        return $this->belongsToMany(Payment::class, 'payments_pivot', 'recurring_payment_profile_id', 'payment_id');
    }

    public function firstPayment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'first_payment_id')->withSpam();
    }

    public function scopeWithFirstPayment($query): Builder
    {
        return $query->addSelect(['first_payment_id' => function ($query) {
            $query->select('payments_pivot.payment_id')
                ->from('payments_pivot')
                ->whereColumn('payments_pivot.recurring_payment_profile_id', 'recurring_payment_profiles.id')
                ->leftJoin('payments', 'payments.id', 'payments_pivot.payment_id')
                ->orderBy('payments.created_at')
                ->limit(1);
        }])->with(['firstPayment']);
    }

    public function lastPaymentAttempt(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'last_payment_attempt_id');
    }

    public function scopeWithLastPaymentAttempt($query): Builder
    {
        return $query->addSelect(['last_payment_attempt_id' => function ($query) {
            $query->select('payments_pivot.payment_id')
                ->from('payments_pivot')
                ->whereColumn('payments_pivot.recurring_payment_profile_id', 'recurring_payment_profiles.id')
                ->leftJoin('payments', 'payments.id', 'payments_pivot.payment_id')
                ->orderByDesc('payments.created_at')
                ->limit(1);
        }])->with(['lastPaymentAttempt']);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(Variant::class, 'productinventory_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function last_transaction(): HasOne
    {
        return $this->hasOne(Transaction::class)
            ->orderBy('id', 'DESC');
    }

    public function sponsorship(): BelongsTo
    {
        return $this->belongsTo(Sponsorship::class);
    }

    public function sponsor(): HasOne
    {
        return $this->HasOne(Sponsor::class, 'order_item_id', 'productorderitem_id');
    }

    public function scopeStatus(Builder $query, $status): Builder
    {
        return $query->where('status', ucfirst($status));
    }

    /**
     * Scope: Active profiles.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeActive($query)
    {
        $query->where('status', RecurringPaymentProfileStatus::ACTIVE);
    }

    /**
     * Scope: Suspended profiles.
     */
    public function scopeSuspended(Builder $query): void
    {
        $query->where('status', RecurringPaymentProfileStatus::SUSPENDED);
    }

    /**
     * Scope: Cancelled profiles.
     */
    public function scopeCancelled(Builder $query): void
    {
        $query->where('status', RecurringPaymentProfileStatus::CANCELLED);
    }

    /**
     * Scope: Expired profiles.
     */
    public function scopeExpired(Builder $query): void
    {
        $query->where('status', RecurringPaymentProfileStatus::EXPIRED);
    }

    /**
     * Scope: Manual profiles.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeManual($query)
    {
        $query->where('is_manual', true);
    }

    /**
     * Scope: Locked profiles.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeLocked($query, $locked = true)
    {
        $query->where('is_locked', $locked);
    }

    /**
     * Scope: Chargeable profiles.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \DateTimeInterface|string
     */
    public function scopeChargeable($query, $nextBillDate = 'today')
    {
        $query->active();
        $query->where('is_manual', false);
        $query->where('is_locked', false);
        $query->where('payment_mutex', false);

        $query->where(function ($query) use ($nextBillDate) {
            $query->where(function ($query) use ($nextBillDate) {
                $query->where('next_billing_date', '<=', fromLocalFormat($nextBillDate, 'date'));
                $query->whereNull('next_attempt_date');
            });

            $query->orWhere('next_attempt_date', '<=', fromLocalFormat($nextBillDate, 'date'));
        });

        $query->where(function ($query) {
            $query->whereNull('num_cycles_remaining');
            $query->orWhere('num_cycles_remaining', '>', 0);
        });

        $query->where('amt', '>', 0);
    }

    /**
     * Scope: Billable profiles.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeBillable($query)
    {
        $query->chargeable();
        $query->where('failed_payment_count', '=', 0);
        $query->locked(false);
    }

    /**
     * Scope: The profiles which failed when last processed.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeFailed($query)
    {
        $query->chargeable();
        $query->where('next_attempt_date', '<=', fromLocalFormat('today', 'date'));
        $query->where('failed_payment_count', '>', 0);
        $query->where('outstanding_balance', '>', 0);
        $query->locked(false);
    }

    /**
     * Attribute Accessor: Is Tax Receiptable
     *
     * @return bool
     */
    public function getIsTaxReceiptableAttribute()
    {
        if ($this->product && $this->product->is_tax_receiptable) {
            return true;
        }

        if ($this->sponsorship && sys_get('sponsorship_tax_receipts')) {
            return true;
        }

        return false;
    }

    /**
     * Attribute Accessor: GL Code
     *
     * @return string|null
     */
    public function getGlCodeAttribute()
    {
        return $this->order_item->gl_code ?? null;
    }

    /**
     * Attribute mask: payment_string
     *
     * @return string|null
     */
    public function getPaymentStringAttribute()
    {
        $params = [
            'amount' => money($this->total_amt, $this->currency_code),
            'day' => $this->billing_period_day,
        ];

        if ($this->billing_period === 'Week') {
            return trans('payments.recurring.weekly', $params);
        }

        if ($this->billing_period === 'SemiMonth') {
            return trans('payments.recurring.bi_monthly', $params);
        }

        if (trim($this->billing_period) === 'Quarter') {
            return trans('payments.recurring.quarterly', $params);
        }

        if (trim($this->billing_period) === 'Month') {
            return trans('payments.recurring.monthly', $params);
        }

        if (trim($this->billing_period) === 'SemiYear') {
            return trans('payments.recurring.semi_yearly', $params);
        }

        if (trim($this->billing_period) === 'Year') {
            return trans('payments.recurring.yearly', $params);
        }
    }

    /**
     * Attribute Mask: total_amt (the total amount to charge each cycle)
     *
     * @return float
     */
    public function getTotalAmtAttribute()
    {
        return $this->amt + $this->tax_amt + $this->shipping_amt + $this->dcc_amount;
    }

    /**
     * Attribute Accessor: Final Billing Date
     *
     *   NOTE: Not to be confused with `final_payment_due_date` which actually
     *   represents two things:
     *
     *      1) The date a profile was cancelled
     *      2) The date after which no payments should be processed
     *
     * @return \DateTimeInterface|null
     */
    public function getFinalBillingDateAttribute()
    {
        if ($this->num_cycles_remaining === null) {
            return null;
        }

        if ($this->num_cycles_remaining === 0) {
            return ($this->last_payment_date ?? $this->profile_start_date)->copy()->asDate();
        }

        $date = $this->next_billing_date->copy();

        for ($i = 1; $i < $this->num_cycles_remaining; $i++) {
            app(RecurringPaymentProfileService::class)->addBillingIntervalToDate($this, $date);
        }

        return $date;
    }

    /**
     * Set the Billing Period and the Billing Frequency.
     *
     * @param string $period
     * @return void
     */
    public function setBillingPeriodAttribute($period)
    {
        switch ($period) {
            case 'daily':
            case 'Day':
                $this->billing_frequency = 365;
                $this->attributes['billing_period'] = 'Day';
                break;
            case 'weekly':
            case 'Week':
                $this->billing_frequency = 52;
                $this->attributes['billing_period'] = 'Week';
                break;
            case 'biweekly':
            case 'SemiMonth':
                $this->billing_frequency = 26;
                $this->attributes['billing_period'] = 'SemiMonth';
                break;
            case 'monthly':
            case 'Month':
                $this->billing_frequency = 12;
                $this->attributes['billing_period'] = 'Month';
                break;
            case 'quarterly':
            case 'Quarter':
                $this->billing_frequency = 4;
                $this->attributes['billing_period'] = 'Quarter';
                break;
            case 'biannually':
            case 'SemiYear':
                $this->billing_frequency = 2;
                $this->attributes['billing_period'] = 'SemiYear';
                break;
            case 'annually':
            case 'yearly':
            case 'Year':
                $this->billing_frequency = 1;
                $this->attributes['billing_period'] = 'Year';
                break;
            default:
                throw new MessageException('Billing period must be one of: Day, Week, SemiMonth, Month, Quarter, SemiYear or Year.');
        }
    }

    /**
     * The ISO-8601 descriptor for the Billing Period.
     *
     * @return string|null
     */
    public function getIso8601BillingPeriodAttribute(): ?string
    {
        switch ($this->billing_period) {
            case 'Day':       return 'P1D';
            case 'Week':      return 'P1W';
            case 'SemiMonth': return 'P2W';
            case 'Month':     return 'P1M';
            case 'Quarter':   return 'P3M';
            case 'SemiYear':  return 'P6M';
            case 'Year':      return 'P1Y';
            default:          return null;
        }
    }

    /**
     * The description associated with the Billing Period.
     *
     * @return string|null
     */
    public function getBillingPeriodDescriptionAttribute()
    {
        switch ($this->billing_period) {
            case 'Day':       return 'Daily';
            case 'Week':      return 'Weekly';
            case 'SemiMonth': return 'Every 2 Weeks';
            case 'Month':     return 'Monthly';
            case 'Quarter':   return 'Every 3 Months';
            case 'SemiYear':  return 'Every 6 Months';
            case 'Year':      return 'Every 365 Days';
        }
    }

    /**
     * The description associated with the billing period day.
     *
     * @return string|null
     */
    public function getBillingPeriodDayAttribute()
    {
        switch ($this->billing_period) {
            case 'Day':
            case 'Week':
            case 'SemiMonth': return $this->next_billing_date->format('l');
            case 'Month':
            case 'Quarter':
            case 'SemiYear':
            case 'Year':      return $this->next_billing_date->format('jS');
        }
    }

    /**
     * The next possible billing date based on the current billing
     * period and current next billing date.
     *
     * @return \DateTime
     */
    public function getNextPossibleBillingDateAttribute(?Carbon $from = null)
    {
        $from = $from ? Date::instance($from) : null;

        /** @var \Ds\Services\RecurringPaymentProfileService */
        $rppService = app(RecurringPaymentProfileService::class);

        return $rppService->getNextPossibleBillingDate($this, $from ? Date::instance($from) : null);
    }

    /**
     * The last billing date based on the current next billing date.
     *
     * @return \Ds\Domain\Shared\DateTime|null
     */
    public function getLastBillingDateAttribute()
    {
        if (! $this->next_billing_date) {
            return null;
        }

        $lastDate = $this->next_billing_date->copy();

        app(RecurringPaymentProfileService::class)->subBillingIntervalFromDate($this, $lastDate);

        if ($lastDate->lt($this->profile_start_date->copy()->startOfDay())) {
            return null;
        }

        return $lastDate;
    }

    /**
     * Calculate the first possible start date.
     *
     * @param string|null $type
     * @param int|string|null $monthDay
     * @param int|string|null $weekDay
     * @param string|null $initialCharge
     * @param \DateTime|string|null $delayUntilAfterDate
     * @param \DateTime|string|null $startDate
     * @return \Ds\Domain\Shared\Date
     */
    public function getFirstPossibleStartDate($type, $monthDay, $weekDay, $initialCharge, $delayUntilAfterDate = null, $startDate = null)
    {
        $type = (string) $type;
        $startDate = (fromLocal($startDate) ?? fromLocal('today'))->asDate();
        $delayUntilAfterDate = optional(fromLocal($delayUntilAfterDate))->asDate();

        $this->setBillingCycleAnchorForFirstPossibleStartDate(
            $type,
            nullable_cast('int', $monthDay),
            nullable_cast('int', $weekDay),
            $startDate
        );

        return app(RecurringPaymentProfileService::class)->getFirstPossibleStartDate(
            $this,
            $type,
            $initialCharge === 'one-time',
            $delayUntilAfterDate,
            $startDate
        );
    }

    public function setBillingCycleAnchorForFirstPossibleStartDate(string $type, ?int $dayOfMonth, ?int $dayOfWeek, Date $startDate): void
    {
        $billingCycleAnchor = $startDate->copy();

        if ($type === 'natural') {
            $dayOfMonth = (int) $billingCycleAnchor->format('j');
            $dayOfWeek = (int) $billingCycleAnchor->format('w');
        }

        switch ($this->billing_period) {
            case BillingPeriod::WEEK:
            case BillingPeriod::SEMI_MONTH:
                $dayOfWeek = max(0, min(7, $dayOfWeek));

                // Convert any ISO-8601 Sunday values to the numeric representation
                if ($dayOfWeek === 7) {
                    $dayOfWeek = 0;
                }

                $this->billing_cycle_anchor = $billingCycleAnchor->previous($dayOfWeek);
                break;
            case BillingPeriod::MONTH:
            case BillingPeriod::QUARTER:
            case BillingPeriod::SEMI_YEAR:
            case BillingPeriod::YEAR:
                $dayOfMonth = max(1, min(31, $dayOfMonth));

                while ($billingCycleAnchor->copy()->endOfMonth()->format('j') < $dayOfMonth) {
                    $billingCycleAnchor->subMonthWithoutOverflow();
                }

                $this->billing_cycle_anchor = $billingCycleAnchor->setDay($dayOfMonth);
                break;
            default:
                $this->billing_cycle_anchor = null;
        }
    }

    /**
     * Locks the profile.
     *
     * @return void
     */
    public function lockProfile()
    {
        // Check for existing lock
        if ($this->payment_mutex) {
            throw new MessageException('Profile is aleady locked.');
        }

        $this->payment_mutex = 1;
        $this->save();
    }

    /**
     * Unlocks the profile.
     *
     * @return void
     */
    public function unlockProfile()
    {
        $this->payment_mutex = 0;
        $this->save();
    }

    /**
     * Locks the profile and starts a new transaction.
     *
     * @param bool $requires_payment_method
     * @param \Ds\Domain\Commerce\Money $amount
     * @return \Ds\Models\Transaction
     */
    public function createTransaction($requires_payment_method = true, Money $amount = null)
    {
        // Check for existing lock
        if ($this->payment_mutex) {
            throw new MessageException('Profile is currently locked.');
        }

        // check for payment method
        if ($requires_payment_method && ! isset($this->paymentMethod->paymentProvider)) {
            throw new MessageException('Payment method required.');
        }

        // Create new transaction
        $transaction = new Transaction;
        $transaction->transaction_status = 'New';
        $transaction->transaction_type = 'Recurring';
        $transaction->tax_receipt_type = sys_get('tax_receipt_type');
        $transaction->recurring_payment_profile_id = $this->id;

        if ($requires_payment_method) {
            $transaction->payment_method_id = $this->payment_method_id;
            $transaction->payment_method_type = $this->paymentMethod->paymentProvider->provider;
            $transaction->payment_method_desc = $this->paymentMethod->account_number;
        }

        $transaction->order_time = new Carbon;
        $transaction->functional_currency_code = sys_get('dpo_currency');
        $transaction->payment_status = 'None';
        $transaction->ship_to_name = $this->ship_to_name;
        $transaction->ship_to_street = $this->ship_to_street;
        $transaction->ship_to_street2 = $this->ship_to_street2;
        $transaction->ship_to_city = $this->ship_to_city;
        $transaction->ship_to_state = $this->ship_to_state;
        $transaction->ship_to_zip = $this->ship_to_zip;
        $transaction->ship_to_country = $this->ship_to_country;
        $transaction->ship_to_phone_num = $this->ship_to_phone_num;

        if ($amount) {
            $transaction->amt = $amount->amount;
            $transaction->currency_code = $amount->currency_code;
        } else {
            $transaction->amt = $this->total_amt;
            $transaction->currency_code = $this->currency_code;
            $transaction->tax_amt = $this->tax_amt;
            $transaction->shipping_amt = $this->shipping_amt;
            $transaction->dcc_type = $this->dcc_type;
            $transaction->dcc_amount = $this->dcc_amount;
        }

        $transaction->transactionLog('creating new transaction');

        return $transaction;
    }

    /**
     * Creates a new recurring payment profile based on a specific
     * order item, cart and PaymentMethod.
     *
     * @return \Ds\Models\RecurringPaymentProfile
     */
    public static function createUsingOrderItemAndCartAndPaymentMethod($item, $cart, PaymentMethod $paymentMethod = null)
    {
        if ($item->recurringPaymentProfile) {
            return $item->recurringPaymentProfile;
        }

        $variant = Variant::with('product')->find($item->productinventoryid);
        $sponsorship = ($item->sponsorship_id) ? \Ds\Domain\Sponsorship\Models\Sponsorship::find($item->sponsorship_id) : null;

        $taxAmount = (float) db_var('SELECT amount FROM productorderitemtax WHERE orderitemid = %d', $item->id);
        $taxAmount = money($taxAmount, $cart->currency_code)->getAmount();

        $rpp = new self;
        $rpp->member_id = $cart->member_id;
        $rpp->status = RecurringPaymentProfileStatus::ACTIVE;
        $rpp->subscriber_name = ($paymentMethod) ? (trim($paymentMethod->billing_first_name . ' ' . $paymentMethod->billing_last_name)) : ($cart->member->display_name);
        $rpp->profile_start_date = $cart->confirmationdatetime ?? fromUtc('now');
        $rpp->profile_reference = $cart->client_uuid;
        $rpp->aggregate_amount = 0;
        $rpp->description = $item->description ?? '(none)';
        $rpp->max_failed_payments = sys_get('rpp_retry_attempts');
        $rpp->auto_bill_out_amt = sys_get('rpp_auto_bill_out_amt');
        $rpp->ship_to_name = trim($cart->shipping_first_name . ' ' . $cart->shipping_last_name);
        $rpp->ship_to_street = $cart->shipaddress1;
        $rpp->ship_to_street2 = $cart->shipaddress2;
        $rpp->ship_to_city = $cart->shipcity;
        $rpp->ship_to_state = $cart->shipstate;
        $rpp->ship_to_zip = $cart->shipzip;
        $rpp->ship_to_country = $cart->shipcountry;
        $rpp->ship_to_phone_num = $cart->shipphone;
        $rpp->transaction_type = ($sponsorship ?? $variant->is_donation ?? null) ? 'Donation' : 'Standard';
        $rpp->init_amt = $item->recurring_with_initial_charge ? $item->total + $taxAmount + $item->dcc_recurring_amount : 0.00;
        $rpp->amt = $item->recurring_amount;
        $rpp->currency_code = $cart->currency_code;
        $rpp->tax_amt = $taxAmount;
        $rpp->billing_period = $item->recurring_frequency;
        $rpp->num_cycles_completed = 0;
        $rpp->last_payment_date = null;
        $rpp->last_payment_amt = 0;
        $rpp->is_manual = (! isset($paymentMethod));
        $rpp->payment_method_id = $paymentMethod->id ?? null;
        $rpp->productorder_id = $item->productorderid;
        $rpp->productorderitem_id = $item->id;
        $rpp->productinventory_id = $item->productinventoryid;
        $rpp->product_id = $variant->productid ?? null;
        $rpp->sponsorship_id = $sponsorship->id ?? null;

        $rpp->dcc_enabled_by_customer = (bool) $cart->dcc_enabled_by_customer;

        if ($cart->dcc_enabled_by_customer) {
            $rpp->dcc_per_order_amount = $cart->dcc_per_order_amount;
            $rpp->dcc_rate = $cart->dcc_rate;
            $rpp->dcc_type = $cart->dcc_type;
            $rpp->dcc_amount = $item->dcc_recurring_amount;
        } else {
            $rpp->dcc_per_order_amount = 0;
            $rpp->dcc_rate = 0;
            $rpp->dcc_type = null;
            $rpp->dcc_amount = 0;
        }

        if ($rpp->init_amt) {
            $rpp->last_payment_date = $cart->confirmationdatetime;
            $rpp->last_payment_amt = $rpp->init_amt;
            $rpp->aggregate_amount = $rpp->init_amt;
            $rpp->num_cycles_completed = 1;
        }

        $rpp->profile_start_date = $rpp->getFirstPossibleStartDate(
            $variant->product->recurring_type ?? sys_get('rpp_default_type'),
            $item->recurring_day,
            $item->recurring_day_of_week,
            $item->recurring_with_initial_charge ? 'one-time' : null,
            $item->recurring_starts_on ?? 'today'
        );

        $rpp->next_billing_date = $rpp->profile_start_date->copy();

        if ($item->recurring_cycles) {
            $rpp->num_cycles_remaining = $item->recurring_cycles - $rpp->num_cycles_completed;
        }

        if ($item->recurring_ends_on) {
            $rpp->num_cycles_remaining = $rpp->next_billing_date->lessThan($item->recurring_ends_on)
                ? count(CarbonPeriod::create($rpp->next_billing_date, $rpp->iso8601_billing_period, $item->recurring_ends_on))
                : 0;
        }

        return $rpp;
    }

    /**
     * Manually charge a recurring payment profile.
     *
     * @param \Ds\Models\PaymentMethod|string $method
     * @param array $options ['transaction_id','reason_code','update_next_bill']
     * @return \Ds\Models\Transaction
     */
    public function manualCharge($method, $options = null)
    {
        // make sure its an active profile
        if ($this->status !== RecurringPaymentProfileStatus::ACTIVE) {
            throw new MessageException('Unable to charge profile ' . $this->profile_id . '. It is not an active profile (' . $this->status . ').');
        }

        if ($this->total_amt <= 0) {
            throw new MessageException("Unable to charge profile {$this->profile_id}. Can only charge amounts greater than zero.");
        }

        // create a txn to charge
        $txn = $this->createTransaction(false);

        // store the original next bill (for some reason,
        // the built in ->original() function doesn't do
        // the trick)
        // $original_next_bill = $this->next_billing_date->copy();

        // offline payment type (cash/check/other)
        if (is_string($method) && in_array($method, ['eft', 'cash', 'check', 'other'])) {
            $txn->payment_method_id = null;
            $txn->payment_method_type = $method;
            $txn->transaction_id = $options['transaction_id'] ?? null;
            $txn->reason_code = $options['reason_code'] ?? null;

        // stored payment method
        } elseif (is_a($method, PaymentMethod::class)) {
            $txn->payment_method_id = $method->id;
            $txn->payment_method_type = $method->paymentProvider->provider;
            $txn->payment_method_desc = $method->account_number;

        // invalid payment method
        } else {
            throw new MessageException('Method supplied for a manual charge is invalid.');
        }

        // make a note on the txn that its a manual charge
        $txn->transactionLog('initiating a manual charge' . ((auth()->user()) ? ' (' . user('full_name') . ')' : ''));

        // process the transaction
        app(TransactionRepository::class)->handleTransaction($this, $txn);

        // reset next bill date back to what it was
        // if (isset($options['update_next_bill']) && ($options['update_next_bill'] === false)) {
        //  $this->next_billing_date = $original_next_bill;
        //  $this->save();
        // }

        // return the processed transaction
        return $txn;
    }

    /**
     * Refresh the aggregate amount
     *
     * @return \Ds\Models\RecurringPaymentProfile
     */
    public function refreshAggregateAmount()
    {
        $this->aggregate_amount = $this->transactions()
            ->succeeded()
            ->notRefunded()
            ->get()
            ->reduce(function ($amount, $transaction) {
                return $amount + $transaction->amt;
            }, $this->init_amt);

        $this->save();

        return $this;
    }

    /**
     * Send a notification to the sponsor
     * (start, stop, anniversary, b-day)
     *
     * @param \Ds\Models\Email|string $email
     * @return bool
     */
    public function notify($email)
    {
        $params = [
            'profile_start_date' => $this->profile_start_date,
            'profile_next_bill_date' => toUtcFormat($this->next_billing_date, 'l, F jS, Y'),
            'profile_description' => $this->sponsorship ? "Sponsorship of {$this->sponsorship->first_name} {$this->sponsorship->last_name}" : $this->product->name ?? $this->description,
            'profile_amount' => money($this->amt, $this->currency_code),
            'profile_frequency' => $this->billing_period_description,
            'payment_date' => toUtcFormat($this->last_transaction->order_time ?? null, 'F d, Y \a\t g:ia'),
            'payment_transaction' => $this->last_transaction->transaction_id ?? null,
            'payment_status' => $this->last_transaction->payment_status ?? null,
            'payment_reason_code' => $this->last_transaction->reason_code ?? null,
            'payment_method_desc' => $this->last_transaction->payment_method_desc ?? null,
            'payment_amt' => $this->last_transaction ? money($this->last_transaction->amt, $this->currency_code) : null,
            'product_name' => $this->product->name ?? null,
            'sponsorship_first_name' => $this->sponsorship->first_name ?? null,
            'sponsorship_last_name' => $this->sponsorship->last_name ?? null,
            'sponsorship_reference' => $this->sponsorship->reference_number ?? null,
        ];

        return $this->member->notify($email, $params);
    }

    /**
     * Cancel a profile.
     *
     * @param string $reason Optional reason for canceling the profile
     * @return void
     */
    public function cancelProfile($reason = '')
    {
        $this->status = RecurringPaymentProfileStatus::CANCELLED;
        $this->final_payment_due_date = now()->format('Y-m-d H:i:s');
        $this->cancel_reason = $reason;
        $this->save();
    }

    /**
     * Update cancel reason
     *
     * @param string $reason Optional reason for canceling the profile
     * @return void
     */
    public function updateCancelReason(string $reason = '')
    {
        $this->cancel_reason = $reason;
        $this->save();
    }

    /**
     * Activate the profile.
     */
    public function activateProfile()
    {
        if ($this->status === RecurringPaymentProfileStatus::ACTIVE) {
            return;
        }

        $this->status = RecurringPaymentProfileStatus::ACTIVE;
        $this->next_billing_date = app(RecurringPaymentProfileService::class)->getNextPossibleBillingDate($this, fromDate($this->last_payment_date));
        $this->next_attempt_date = null;
        $this->outstanding_balance = 0;
        $this->failed_payment_count = 0;
        $this->save();
    }

    /**
     * Liquid representation of model.
     */
    public function toLiquid()
    {
        return \Ds\Domain\Theming\Liquid\Drop::factory($this, 'Subscription');
    }
}
