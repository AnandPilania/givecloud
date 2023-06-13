<?php

namespace Ds\Models;

use Carbon\Carbon;
use Ds\Domain\Commerce\Currency;
use Ds\Domain\Commerce\Exceptions\GatewayException;
use Ds\Domain\Commerce\Models\PaymentProvider;
use Ds\Domain\Commerce\Responses\TransactionResponse;
use Ds\Domain\Commerce\Support\TaxCloud\TaxCloudRepository;
use Ds\Domain\Kiosk\Models\Kiosk;
use Ds\Domain\Shared\Exceptions\MessageException;
use Ds\Domain\Sponsorship\Models\PaymentOption;
use Ds\Domain\Sponsorship\Models\Sponsorship;
use Ds\Domain\Theming\Liquid\Liquidable;
use Ds\Eloquent\Hashids;
use Ds\Eloquent\Permissions;
use Ds\Eloquent\SoftDeleteUserstamp;
use Ds\Eloquent\Spammable;
use Ds\Eloquent\Userstamps;
use Ds\Enums\ProductType;
use Ds\Http\Resources\DonationForms\DonationFormResource;
use Ds\Illuminate\Database\Eloquent\Factories\HasFactory;
use Ds\Illuminate\Database\Eloquent\Model;
use Ds\Jobs\Orders\SendSupporterContributionAcknowledgmentMail;
use Ds\Models\Observers\OrderObserver;
use Ds\Models\Traits\HasExternalReferences;
use Ds\Models\Traits\HasLedgerEntries;
use Ds\Models\Traits\HasUserAgent;
use Ds\Services\ContributionService;
use Ds\Services\DonorCoversCostsService;
use Ds\Services\LocaleService;
use Ds\Services\Order\OrderAddItemService;
use Ds\Services\OrderService;
use Ds\Services\PaymentService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class Order extends Model implements Liquidable
{
    use HasFactory;
    use HasExternalReferences;
    use Hashids;
    use HasLedgerEntries;
    use HasUserAgent;
    use Permissions;
    use SoftDeletes;
    use SoftDeleteUserstamp;
    use Spammable;
    use Userstamps;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'productorder';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'createddatetime',
        'confirmationdatetime',
        'started_at',
        'refunded_at',
        'payment_lock_at',
        'alt_data_updated_at',
        'deleted_at',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'iscomplete' => 'boolean',
        'isrecurring' => 'boolean',
        'istribute' => 'boolean',
        'shipping_amount' => 'double',
        'is_processed' => 'boolean',
        'is_spam' => 'boolean',
        'is_test' => 'boolean',
        'marked_at_spam_at' => 'datetime',
        'member_id' => 'integer',
        'productinventoryid' => 'integer',
        'shipping_method_id' => 'integer',
        'total_weight' => 'double',
        'total_qty' => 'integer',
        'discount' => 'double',
        'subtotal' => 'double',
        'taxtotal' => 'double',
        'dcc_total_amount' => 'double',
        'totalamount' => 'double',
        'original_totalamount' => 'double',
        'functional_exchange_rate' => 'double',
        'functional_total' => 'double',
        'refunded_amt' => 'double',
        'is_pos' => 'boolean',
        'check_amt' => 'double',
        'check_date' => 'date',
        'cash_received' => 'double',
        'cash_change' => 'double',
        'recurring_items' => 'integer',
        'download_items' => 'integer',
        'shippable_items' => 'integer',
        'dp_sync_order' => 'boolean',
        'doublethedonation_registered' => 'boolean',
        'total_savings' => 'double',
        'is_free_shipping' => 'boolean',
        'is_test' => 'boolean',
        'ship_to_billing' => 'boolean',
        'ordered_at' => 'datetime',
        'email_opt_in' => 'boolean',
        'is_anonymous' => 'boolean',
    ];

    /**
     * Default attributes and values.
     *
     * @var array
     */
    protected $attributes = [
        'is_processed' => false,
        'is_spam' => false,
        'is_test' => false,
        'iscomplete' => false,
        'isrecurring' => false,
        'istribute' => false,
        'recurring_items' => 0,
        'download_items' => 0,
        'shippable_items' => 0,
        'total_items' => 0,
        'total_qty' => 0,
        'total_weight' => 0,
        'discount' => 0,
        'subtotal' => 0,
        'taxtotal' => 0,
        'shipping_amount' => 0,
        'admin_amount' => 0,
        'totalamount' => 0,
        'total_savings' => 0,
        'functional_exchange_rate' => 1,
        'functional_total' => 0,
        'is_pos' => false,
        'is_anonymous' => true,
        'auth_attempts' => 0,
        'ship_to_billing' => 0,
        'is_free_shipping' => false,
        'tax_receipt_type' => 'single',
        'dcc_enabled_by_customer' => false,
        'dcc_total_amount' => 0,
        'email_opt_in' => false,
        'send_confirmation_emails' => 1,
        'dp_sync_order' => null,
        'doublethedonation_registered' => false,
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'shipping_address_html',
        'billing_address_html',
        'available_shipping_methods',
        'source_and_date_string',
        'recurring_description',
        'fa_icon',
        'balance_amt',
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var string[]|bool
     */
    protected $guarded = [
        'id',
        'recurring_items',
        'download_items',
        'shippable_items',
        'totalamount',
        'subtotal',
        'taxtotal',
        'discount',
        'total_savings',
        'total_qty',
        'total_weight',
        'check_amt',
        'cash_received',
        'cash_change',
    ];

    /**
     * Fixed set of payment types.
     *
     * @var array
     */
    protected $payment_types = [
        'cc',
        'ach',
        'free',
        'cash',
        'check',
        'other',
    ];

    /**
     * System order sources.
     *
     * @var array
     */
    protected static $system_sources = [
        'Import',
        'Kiosk',
        'Messenger',
        'Point of Sale (POS)',
        'Web',
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        self::observe(new OrderObserver);
    }

    public function contribution(): BelongsTo
    {
        return $this->belongsTo(Contribution::class);
    }

    public function refundedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'refunded_by');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function accountType(): BelongsTo
    {
        return $this->belongsTo(AccountType::class);
    }

    public function shippingMethod(): BelongsTo
    {
        return $this->belongsTo(ShippingMethod::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'productorderid');
    }

    public function recurringItems(): HasMany
    {
        return $this->items()
            ->where('recurring_amount', '>', 0);
    }

    public function markedAsSpamBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_as_spam_by');
    }

    /**
     * Relationship: Products
     */
    public function products(): Builder
    {
        return Product::query()
            ->join('productinventory', 'productinventory.productid', '=', 'product.id')
            ->join('productorderitem', 'productorderitem.productinventoryid', '=', 'productinventory.id')
            ->where('productorderitem.productorderid', '=', $this->id);
    }

    public function taxReceipts(): BelongsToMany
    {
        return $this->belongsToMany(TaxReceipt::class, 'tax_receipt_line_items', 'order_id', 'tax_receipt_id')
            ->using(TaxReceiptLineItem::class);
    }

    public function promoCodes(): BelongsToMany
    {
        return $this->belongsToMany(PromoCode::class, 'order_promocodes', 'order_id', 'promocode', 'id', 'code')
            ->withPivot('id')
            ->distinct();
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function paymentProvider(): BelongsTo
    {
        return $this->belongsTo(PaymentProvider::class);
    }

    /**
     * Relationship User
     */
    public function altDataUpdatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'alt_data_updated_by');
    }

    public function payments(): BelongsToMany
    {
        return $this->belongsToMany(Payment::class, 'payments_pivot', 'order_id', 'payment_id')->withSpam();
    }

    public function latestPayment(): HasOneThrough
    {
        return $this->hasOneThrough(Payment::class, PaymentPivot::class, 'order_id', 'id', null, 'payment_id')->withSpam()->latest();
    }

    public function successfulPayments(): BelongsToMany
    {
        return $this->payments()
            ->succeededOrPending();
    }

    /**
     * Scope: Orders using text to give.
     */
    public function scopeConversation(Builder $query): Builder
    {
        return $query->where('source', 'Messenger');
    }

    /**
     * Scope: Orders that have been processed/paid/submitted.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopePaid($query)
    {
        return $query->whereNotNull('confirmationdatetime');
    }

    /**
     * Scope: Orders from this year.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeThisYear($query)
    {
        return $query->whereBetween('confirmationdatetime', [
            toUtc(fromLocal('now')->startOfYear()),
            toUtc(fromLocal('now')->endOfYear()),
        ]);
    }

    /**
     * Scope: Orders ordered before a date
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $date
     */
    public function scopeOrderedBefore(Builder $query, $date): Builder
    {
        return $query->where('ordered_at', '<', Carbon::parse($date));
    }

    /**
     * Scope: Orders ordered after a date
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $date
     */
    public function scopeOrderedAfter(Builder $query, $date): Builder
    {
        return $query->where('ordered_at', '>', Carbon::parse($date));
    }

    /**
     * Scope: Orders updated before a date
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $date
     */
    public function scopeUpdatedBefore(Builder $query, $date): Builder
    {
        return $query->where('updated_at', '<', Carbon::parse($date));
    }

    /**
     * Scope: Orders created after a date
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $date
     */
    public function scopeUpdatedAfter(Builder $query, $date): Builder
    {
        return $query->where('updated_at', '>', Carbon::parse($date));
    }

    /**
     * Scope: Orders from this month.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeThisMonth($query)
    {
        return $query->whereBetween('ordered_at', [
            toUtc(fromLocal('now')->startOfMonth()),
            toUtc(fromLocal('now')->endOfMonth()),
        ]);
    }

    /**
     * Scope: Orders from today.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeToday($query)
    {
        return $query->whereBetween('confirmationdatetime', [
            toUtc(fromLocal('now')->startOfDay()),
            toUtc(fromLocal('now')->endOfDay()),
        ]);
    }

    /**
     * Scope: Orders that have NOT been processed (abandoned).
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeAbandoned($query)
    {
        return $query->whereNull('confirmationdatetime')
            ->where('is_pos', 0);
    }

    /**
     * Scope: Pre-checkout
     * - no checkout data
     * - no response text from payment gateway
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopePreCheckout($query)
    {
        return $query->whereRaw('(billing_first_name is null
                        and billing_last_name is null
                        and billingemail is null
                        and billingaddress1 is null
                        and billingaddress2 is null
                        and billingcity is null
                        and billingstate is null)')
            ->whereNull('response_text');
    }

    /**
     * Scope: Checkout
     * - some checkout data (billing)
     * - no response text from payment gateway
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeCheckout($query)
    {
        return $query->whereRaw('(billing_first_name is not null
                        or billing_last_name is not null
                        or billingemail is not null
                        or billingaddress1 is not null
                        or billingaddress2 is not null
                        or billingcity is not null
                        or billingstate is not null)')
            ->whereNull('response_text');
    }

    /**
     * Scope: Failed Payments
     * - some checkout data (billing)
     * - some response text from payment gateway
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeFailedPayments($query)
    {
        return $query->whereNotNull('response_text');
    }

    /**
     * Scope: Unsync'd orders (not sync'd with DP).
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeUnsynced($query)
    {
        if (dpo_is_enabled()) {
            $query->whereRaw("(IFNULL(alt_contact_id,'') IN ('','0') OR IFNULL(alt_transaction_id,'') IN ('','0')) AND dp_sync_order = 1");
        } else {
            $query->whereRaw('1 = 0');
        }
    }

    /**
     * Scope: Test orders
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeIsTest($query)
    {
        return $query->where('is_test', '=', true);
    }

    /**
     * Scope: Incompleted orders (not shipped)
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeComplete($query)
    {
        if (sys_get('use_fulfillment') === 'never') {
            return $query;
        }

        if (sys_get('use_fulfillment') === 'shipping') {
            return $query->where(function (Builder $query) {
                $query->where('iscomplete', true)
                    ->where('shippable_items', '>', 0);
            })->orWhere('shippable_items', 0);
        }

        return $query->where('iscomplete', '=', true);
    }

    /**
     * Scope: Completed orders (shipped)
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeIncomplete($query)
    {
        if (sys_get('use_fulfillment') === 'never') {
            return $query;
        }

        if (sys_get('use_fulfillment') === 'shipping') {
            return $query->where('iscomplete', false)
                ->where('shippable_items', '>', 0);
        }

        return $query->where('iscomplete', '=', false);
    }

    /**
     * Scope: Refunded orders
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeRefunded($query)
    {
        return $query->where('refunded_amt', '>', 0);
    }

    public function scopeNotFullyRefunded($query): Builder
    {
        return $query->where(function (Builder $query) {
            $query->whereNull('refunded_amt')
                ->orWhereRaw('productorder.functional_total >= (IFNULL(productorder.refunded_amt, 0) * productorder.functional_exchange_rate)');
        });
    }

    /**
     * Require that the order is a cart that can be checked out.
     */
    public function requireCart()
    {
        if ($this->is_paid) {
            throw new MessageException('This contribution has already been charged.');
        }
    }

    public function requiresCaptcha()
    {
        if ($this->is_pos || in_array($this->payment_type, ['paypal', 'wallet_pay'], true)) {
            return false;
        }

        return $this->auth_attempts >= sys_get('ss_auth_attempts');
    }

    /**
     * Require that the order is a cart that was recently checked out.
     */
    public function requireRecentCart()
    {
        if (! $this->is_paid || $this->confirmationdatetime->greaterThan(now()->addHours(12))) {
            throw new MessageException("This contribution can't be updated.");
        }
    }

    public function isForFundraisingForm(): bool
    {
        return $this->items->contains(function (OrderItem $item) {
            $productType = $item->variant->product->type ?? null;

            return $productType === ProductType::DONATION_FORM;
        });
    }

    public function getFundraisingFormAttribute(): ?object
    {
        $item = $this->items->first(function (OrderItem $item) {
            $productType = $item->variant->product->type ?? null;

            return $productType === ProductType::DONATION_FORM;
        });

        return $item ? DonationFormResource::make($item->variant->product)->toObject() : null;
    }

    /**
     * Is there a registered member/account with ths order?
     *
     * @return bool
     */
    public function hasMember()
    {
        return is_numeric($this->member_id) && $this->member_id > 0;
    }

    /**
     * Attribute Accessor: Kiosk
     *
     * @return \Ds\Domain\Kiosk\Models\Kiosk|null
     */
    public function getKioskAttribute()
    {
        return $this->reqcache('kiosk', function () {
            if ($this->source === 'Kiosk') {
                return Kiosk::find($this->source_id);
            }
        });
    }

    /**
     * Attribute Accessor: Tax Receipt
     *
     * @return \Ds\Models\TaxReceipt|null
     */
    public function getTaxReceiptAttribute($value)
    {
        return $this->taxReceipts->first();
    }

    /**
     * Attribute Accessor: Currency
     *
     * @return \Ds\Domain\Commerce\Currency
     */
    public function getCurrencyAttribute()
    {
        return new Currency($this->currency_code);
    }

    /**
     * Attribute Mutator: Currency
     *
     * @param mixed $value
     */
    public function setCurrencyAttribute($value)
    {
        $this->currency_code = $value;
    }

    /**
     * Attribute Mutator: Currency Code
     *
     * @param mixed $value
     */
    public function setCurrencyCodeAttribute($value)
    {
        $this->attributes['currency_code'] = (string) new Currency($value);

        $this->functional_currency_code = sys_get('dpo_currency');
        $this->functional_exchange_rate = Currency::getExchangeRate($this->currency_code, $this->functional_currency_code);

        if ($this->exists && $this->isDirty('currency_code')) {
            foreach ($this->items as $item) {
                if ($item->variant && ! $item->variant->is_donation) {
                    $item->price = money($item->variant->actual_price)
                        ->toCurrency($this->currency_code)
                        ->getAmount();

                    if ($item->is_recurring) {
                        $item->recurring_amount = $item->price;
                    }

                    $item->original_price = $item->price;
                    $item->save();
                }
            }

            $this->calculate();
        }

        if (empty($this->functional_total)) {
            $this->functional_total = $this->totalamount * $this->functional_exchange_rate;
        }
    }

    /**
     * Attribute Mutator: Total Amount
     *
     * @param mixed $value
     */
    public function setTotalamountAttribute($value)
    {
        $this->attributes['totalamount'] = $value;

        $this->functional_total = $value * $this->functional_exchange_rate;
    }

    /**
     * Attribute Mask: balance_amt
     *
     * @return float
     */
    public function getBalanceAmtAttribute()
    {
        return ($this->refunded_amt) ? ($this->totalamount - $this->refunded_amt) : $this->totalamount;
    }

    public function getUsingApplicationFeeBillingAttribute(): bool
    {
        return ! empty($this->latestPayment->application_fee_billing);
    }

    public function getNetTotalAmountAttribute(): float
    {
        if (! $this->using_application_fee_billing || empty($this->latestPayment)) {
            return $this->totalamount;
        }

        return $this->totalamount - $this->latestPayment->application_fee_amount - $this->stripe_fee_amount;
    }

    public function getStripeFeeAmountAttribute(): ?float
    {
        if (empty($this->latestPayment->stripe_fee_amount)) {
            return null;
        }

        // exchange rate in stripe is NULL when currencies are the same
        $exchangeRate = $this->latestPayment->stripe_fee_exchange_rate ?: 1;

        return round($this->latestPayment->stripe_fee_amount / $exchangeRate, 2);
    }

    /**
     * Attribute Mask: cash_change
     *
     * @return int
     */
    public function getCashChangeAttribute($value)
    {
        return abs($value);
    }

    /**
     * Attribute Mask: has_cvc_failure
     *
     * @return bool
     */
    public function getHasCvcFailureAttribute()
    {
        return $this->payments->filter(function ($payment) {
            return $payment->paid && $payment->card_cvc_check === 'fail';
        })->isNotEmpty();
    }

    /**
     * Attribute Mask: has_avs_address_failure
     *
     * @return bool
     */
    public function getHasAvsAddressFailureAttribute()
    {
        return $this->payments->filter(function ($payment) {
            return $payment->paid && $payment->card_address_line1_check === 'fail';
        })->isNotEmpty();
    }

    /**
     * Attribute Mask: has_avs_zip_failure
     *
     * @return bool
     */
    public function getHasAvsZipFailureAttribute()
    {
        return $this->payments->filter(function ($payment) {
            return $payment->paid && $payment->card_address_zip_check === 'fail';
        })->isNotEmpty();
    }

    /**
     * Attribute Mask: has_ip_geography_mismatch
     *
     * @return bool
     */
    public function getHasIpGeographyMismatchAttribute()
    {
        if ($this->is_pos) {
            return false;
        }

        if (in_array($this->payment_type, ['paypal', 'wallet_pay'], true)) {
            return false;
        }

        if (in_array($this->source, ['Import', 'Kiosk'], true)) {
            return false;
        }

        return $this->ip_country && strcasecmp($this->ip_country, $this->billingcountry) !== 0;
    }

    public function scopeWithWarnings(Builder $query)
    {
        $query->leftJoin('payments_pivot', 'productorder.id', 'payments_pivot.order_id')
            ->leftJoin('payments', 'payments.id', 'payments_pivot.payment_id')
            ->where(function (Builder $query) {
                $query->where('payments.paid', true)
                    ->where(function (Builder $query) {
                        $query->where('card_cvc_check', 'fail')
                            ->orWhere('card_address_line1_check', 'fail')
                            ->orWhere('card_address_zip_check', 'fail');
                    });
            })->orWhere(function (Builder $query) {
                $query->whereNotNull('productorder.ip_country')
                    ->where('is_pos', false)
                    ->whereRaw('STRCMP(productorder.ip_country, billingcountry) != 0');
            });
    }

    public function scopeWithoutWarnings(Builder $query)
    {
        $query->complete()
            ->leftJoin('payments_pivot', 'productorder.id', 'payments_pivot.order_id')
            ->leftJoin('payments', 'payments.id', 'payments_pivot.payment_id')
            ->where('payments.paid', true)
            ->where(function (Builder $query) {
                $query->whereNull('card_cvc_check')->orWhere('card_cvc_check', '<>', 'fail');
            })->where(function (Builder $query) {
                $query->whereNull('card_address_line1_check')->orWhere('card_address_line1_check', '<>', 'fail');
            })->where(function (Builder $query) {
                $query->whereNull('card_address_zip_check')->orWhere('card_address_zip_check', '<>', 'fail');
            })->where(function (Builder $query) {
                $query->whereNull('productorder.ip_country')
                    ->orWhere('is_pos', true)
                    ->orWhere(function (Builder $query) {
                        $query->where('is_pos', false)
                            ->whereRaw('STRCMP(productorder.ip_country, billingcountry) = 0');
                    });
            })->when(dpo_is_enabled(), function (Builder $query) {
                $query->where(function (Builder $query) {
                    $query->whereNotNull('alt_contact_id')
                        ->orWhereNotNull('alt_transaction_id');
                });
            });
    }

    /**
     * Attribute Mask: warning_count
     *
     * @return int
     */
    public function getWarningCountAttribute($value)
    {
        $i = 0;
        $i += (int) $this->has_cvc_failure;
        $i += (int) $this->has_avs_address_failure;
        $i += (int) $this->has_avs_zip_failure;

        if (! $this->is_pos) {
            $i += (int) $this->has_ip_geography_mismatch;
        }

        return $i;
    }

    /**
     * Attribute Mask: payment_type_formatted
     *
     * @return string
     */
    public function getPaymentTypeFormattedAttribute($value)
    {
        if ($this->payment_type == 'cash') {
            return 'Cash';
        }

        if ($this->payment_type == 'check') {
            return 'Check';
        }

        if ($this->payment_type == 'ach') {
            return 'ACH';
        }

        if ($this->payment_type == 'free') {
            return 'Free';
        }

        if ($this->payment_type == 'other') {
            return 'Other';
        }

        switch (strtolower($this->billingcardtype)) {
            case 'v':
            case 'vi':
            case 'visa': return 'Visa';
            case 'm':
            case 'mc':
            case 'mastercard':
            case 'master card': return 'MasterCard';
            case 'a':
            case 'am':
            case 'amex':
            case 'american express': return 'Amex';
            case 'discover': return 'Discover';
            case 'paypal': return 'PayPal';
            case 'check':
            case 'cheque':
            case 'checking':
            case 'savings':
            case 'business check':
            case 'personal check':
            case 'business cheque':
            case 'personal cheque':
            case 'business checking':
            case 'personal checking': return 'ACH';
            case 'vault': return 'Secure Account';
        }

        return $this->billingcardtype;
    }

    /**
     * Attribute Mask: payment_type_localized
     *
     * @return string|null
     */
    public function getPaymentTypeLocalizedAttribute(): ?string
    {
        return trans('payments.type')[$this->payment_type_formatted] ?? $this->payment_type_formatted;
    }

    /**
     * Attribute Mask: payment_type_description
     *
     * A nice payment method description for use in email notifications.
     *
     * Examples:
     * - Check (number 992342 dated Mar 3, 2017)
     * - Visa (ending in 2934)
     * - Cash
     * - etc...
     *
     * @return string
     */
    public function getPaymentTypeDescriptionAttribute($value)
    {
        // Check (#9234 dated Mar 5, 2017)
        if ($this->payment_type_formatted == 'Check') {
            $x = [];

            if ($this->check_number) {
                $x[] = '#' . $this->check_number;
            }

            if ($this->check_date) {
                $x[] = trans('payments.description.check.dated', [
                    'date' => $this->check_date->format(trans('payments.description.check.date')),
                ]);
            }

            return $this->payment_type_localized . ((count($x) > 0) ? ' (' . implode(' ', $x) . ')' : '');
        }

        // ACH (personal account ending in 8283)
        if ($this->payment_type_formatted == 'ACH') {
            $x = [];

            if (strtolower($this->billingcardtype) == 'business check') {
                $x[] = trans('payments.description.ach.business');
            }

            if (strtolower($this->billingcardtype) == 'personal check') {
                $x[] = trans('payments.description.ach.personal');
            }

            if ($this->billingcardlastfour) {
                $x[] = trans('payments.description.ending_in') . ' ' . $this->billingcardlastfour;
            }

            if ($this->confirmationnumber) {
                $x[] = '- ' . trans('payments.description.authorization') . ' ' . $this->confirmationnumber;
            }

            return $this->payment_type_localized . ((count($x) > 0) ? ' (' . implode(' ', $x) . ')' : '');
        }

        // Visa (ending in 9932)
        if (in_array($this->payment_type_formatted, ['Visa', 'MasterCard', 'Amex', 'Discover', 'Secure Account'])) {
            $x = [];

            if ($this->billingcardlastfour) {
                $x[] = trans('payments.description.ending_in') . ' ' . $this->billingcardlastfour;
            }

            if ($this->confirmationnumber) {
                $x[] = '- ' . trans('payments.description.authorization') . ' ' . $this->confirmationnumber;
            }

            return $this->payment_type_localized . ((count($x) > 0) ? ' (' . implode(' ', $x) . ')' : '');
        }

        // alternate payment method (ref #234235)
        if ($this->payment_type_formatted == 'Other') {
            $x = [];

            if ($this->payment_other_reference) {
                $x[] = trans('payments.description.ref') . ' #' . $this->payment_other_reference;
            }

            return trans('payments.description.alternate') . ((count($x) > 0) ? ' (' . implode(' ', $x) . ')' : '');
        }

        // PayPal account
        if ($this->payment_type_formatted == 'PayPal') {
            return trans('payments.description.paypal');
        }

        // Cash, etc...
        return $this->payment_type_localized;
    }

    /**
     * Attribute Mask: recurring_description
     *
     * Used to help describe recurring payments to the donor in their email profile.
     *
     * EXAMPLES:
     * --------
     *
     * If there is 1 recurring profile:
     * return "You will be charged $23.00/mth starting July 1st, 2017.";
     *
     * If there is 3 recurring profiles: (concat with "," and " and ")
     * return "You will be charged $23.00/mth starting July 1st, 2017, $12.00/mth starting July 1st, 2017 and $17.00/bi-weekly starting Friday, July 5th, 2017.";
     *
     * If there are NO recurring payments:
     * return null;
     *
     *
     * Final email should look like this:
     *
     * "You were charged [[total_amount]] today by [[payment_type_description]]. [[recurring_description]]"
     * ... which would read ...
     * "You were charged $0.00 by Visa (ending in 2292). You will be charged $23.00/mth starting July 1st, 2017."
     *
     * @return string|null
     */
    public function getRecurringDescriptionAttribute()
    {
        // prep to collection descriptions
        $descriptions = [];

        // loop over each item
        foreach ($this->items as $item) {
            // if it has a recurring payment profile
            if ($item->recurring_description) {
                // append description
                $descriptions[] = $item->recurring_description;
            }
        }

        // if there are no items
        if (count($descriptions) == 0) {
            return null;
        }

        // if there is one
        if (count($descriptions) === 1) {
            return trans_choice('payments.recurring.description', 1, ['description' => $descriptions[0]]);
        }

        // if there is 2 (RARE) or more then 2 (ULTRA RARE)
        $last_item = array_pop($descriptions);

        return trans_choice('payments.recurring.description', count($descriptions), [
            'description' => implode(', ', $descriptions),
            'last' => $last_item,
        ]);
    }

    /**
     * Attribute Mask: fa_icon
     *
     * @return string
     */
    public function getFaIconAttribute()
    {
        if ($this->payment_type == 'cash') {
            return 'fa-money';
        }

        if ($this->payment_type == 'check') {
            return 'fa-pencil-square-o';
        }

        if ($this->payment_type == 'ach') {
            return 'fa-bank';
        }

        if ($this->payment_type == 'bank_account') {
            return 'fa-bank';
        }

        if ($this->payment_type == 'free') {
            return 'fa-fw';
        }

        if ($this->payment_type == 'other') {
            return 'fa-help-circle';
        }

        // brand specific icons
        switch (strtolower($this->billingcardtype)) {
            case 'v':
            case 'vi':
            case 'visa': return 'fa-cc-visa';
            case 'm':
            case 'mc':
            case 'mastercard':
            case 'master card': return 'fa-cc-mastercard';
            case 'a':
            case 'am':
            case 'amex':
            case 'american express': return 'fa-cc-amex';
            case 'discover': return 'fa-cc-discover';
            case 'jcb': return 'fa-cc-jcb';
            case 'paypal': return 'fa-paypal';
            case 'checking':
            case 'checkings':
            case 'saving':
            case 'savings':
            case 'business check':
            case 'business checking':
            case 'business savings':
            case 'personal check':
            case 'personal checking':
            case 'personal savings': return 'fa-bank';
            case 'vault': return 'fa-lock';
            case 'cc':
            case 'creditcard':
            case 'credit card': return 'fa-credit-card';
        }

        switch ($this->paymentProvider->provider ?? null) {
            case 'gocardless': return 'fa-bank';
            case 'paypal': return 'fa-paypal';
        }

        // default icon
        return '';
    }

    public function getIscompleteAttribute($value): bool
    {
        if (! $this->is_fulfillable) {
            return true;
        }

        return (bool) $value;
    }

    public function getIsFulfillableAttribute(): bool
    {
        if (sys_get('use_fulfillment') === 'never') {
            return false;
        }

        if (sys_get('use_fulfillment') === 'always') {
            return true;
        }

        // `use_fulfillment` === `shipping`
        return $this->shippable_items > 0;
    }

    public function getUserCanFulfillAttribute(): bool
    {
        return $this->is_fulfillable && $this->userCan(['fullfill']);
    }

    /**
     * Attribute Mask: is_paid
     *
     * @return bool
     */
    public function getIsPaidAttribute($value)
    {
        return isset($this->confirmationdatetime);
    }

    public function getUsedApplePayAttribute(): bool
    {
        return (bool) $this->successfulPayments->firstWhere('card_wallet', 'apple_pay');
    }

    public function getUsedGooglePayAttribute(): bool
    {
        return (bool) $this->successfulPayments->firstWhere('card_wallet', 'google_pay');
    }

    /**
     * Attribute Mask: is_refunded
     *
     * @return bool
     */
    public function getIsRefundedAttribute($value)
    {
        return $this->is_paid && $this->refunded_amt > 0 && $this->refunded_amt == $this->totalamount;
    }

    public function getIsPartiallyRefundedAttribute($value): bool
    {
        return $this->is_paid && $this->refunded_amt > 0 && $this->refunded_amt < $this->totalamount;
    }

    /**
     * Attribute Mask: is_refundable
     *
     * @return bool
     */
    public function getIsRefundableAttribute($value)
    {
        return $this->is_paid && ! $this->is_refunded;
    }

    /**
     * Attribute Mask: unsynced
     *
     * @return bool
     */
    public function getIsUnsyncedAttribute($value)
    {
        if (! dpo_is_enabled()) {
            return false;
        }

        return $this->dp_sync_order && (empty($this->alt_contact_id) || empty($this->alt_transaction_id));
    }

    /**
     * Attribute Mask: shipping_method_name
     * Easy way to reference the shipping method name.
     *
     * @return string|null
     */
    public function getShippingMethodNameAttribute()
    {
        if ($this->courier_method) {
            return $this->courier_method;
        }

        if ($this->shippingMethod) {
            return $this->shippingMethod->name;
        }

        return null;
    }

    /**
     * Attribute Mask: is_shipping_address_valid
     *
     * Lazy address validation for the shipping address.
     * - Is the shipping address supplied?
     *
     * Future:
     * - Check ZIP format in US (postal code format in other countries)
     * - Address validation API?
     *
     * @return bool
     */
    public function getIsShippingAddressValidAttribute()
    {
        return trim($this->shipcountry) !== ''
            && trim($this->shipstate) !== ''
            && trim($this->shipzip) !== '';
    }

    /**
     * Attribute Mask: billing_display_name
     *
     * @return string|null
     */
    public function getBillingDisplayNameAttribute($value)
    {
        return trim($this->billing_first_name . ' ' . $this->billing_last_name) ?: null;
    }

    /**
     * Attribute Mask: shipping_display_name
     *
     * @return string
     */
    public function getShippingDisplayNameAttribute($value)
    {
        return trim($this->shipping_first_name . ' ' . $this->shipping_last_name);
    }

    /**
     * Attribute Mask: billing_address_html
     *
     * @return string
     */
    public function getBillingAddressHtmlAttribute($value)
    {
        $html = collect([
            $this->billing_display_name,
            address_format(
                $this->billingaddress1,
                $this->billingaddress2,
                $this->billingcity,
                $this->billingstate,
                $this->billingzip,
                $this->billingcountry
            ),
            $this->billingemail,
            $this->billingphone,
        ])->reduce(function ($html, $value) {
            return $value ? "$html{$value}\n" : $html;
        });

        return nl2br(e(trim($html)));
    }

    /**
     * Attribute Mask: shipping_address_html
     *
     * @return string
     */
    public function getShippingAddressHtmlAttribute($value)
    {
        $html = address_format(
            $this->shipaddress1,
            $this->shipaddress2,
            $this->shipcity,
            $this->shipstate,
            $this->shipzip,
            $this->shipcountry
        );

        return nl2br(trim($html));
    }

    /**
     * Attribute Mask: taxable_address
     *
     * @return string
     */
    public function getTaxableAddressAttribute(): string
    {
        return address_format(
            $this->tax_address1,
            $this->tax_address2,
            $this->tax_city,
            $this->tax_state,
            $this->tax_zip,
            $this->tax_country,
            ', '
        );
    }

    /**
     * Attribute Mask: is_locked
     *
     * @return bool
     */
    public function getIsLockedAttribute()
    {
        // Web is default
        return ($this->payment_lock_at) ? true : false;
    }

    /**
     * Attribute Mask: source
     *
     * @return string
     */
    public function getSourceAttribute($value)
    {
        // Web is default
        return ($value) ? $value : 'Web';
    }

    /**
     * Attribute Mask: source_and_date_string
     *
     * @return string
     */
    public function getSourceAndDateStringAttribute()
    {
        if ($this->ordered_at) {
            return toLocalFormat($this->ordered_at) . ' by ' . $this->source;
        }

        return $this->source;
    }

    /**
     * Attribute Mask: vault_id_masked
     *
     * @return string
     */
    public function getVaultIdMaskedAttribute()
    {
        $value = $this->vault_id;

        return ($value != null) ? substr($value, 0, 1) . '******' . substr($value, strlen($value) - 4, strlen($value)) : '';
    }

    /**
     * Attribute Mask: receiptable_amount
     *
     * @return float
     */
    public function getReceiptableAmountAttribute()
    {
        // return the receiptable amount
        return TaxReceipt::getReceiptableAmountFromOrder($this);
    }

    /**
     * Attribute Mask: subtotal_shippable
     *
     * This is the total value of all items in the cart where:
     * - the item is SHIPPABLE
     * - the item is NOT is_free_shipping
     *   (if item.IS_FREE_SHIPPIING, the item's price and
     *    weight should not be taken into account when
     *    calculating shipping totals)
     *
     * @return float
     */
    public function getSubtotalShippableAttribute()
    {
        return collect($this->items)
            ->filter(function ($item) {
                return $item->requires_shipping && ! $item->has_free_shipping;
            })->sum('total');
    }

    /**
     * Attribute Mask: available_shipping_methods (array)
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAvailableShippingMethodsAttribute($value)
    {
        // if we don't have valid shipping info,
        // we can't calculate shipping
        if (! feature('shipping') || ! $this->is_shipping_address_valid || $this->is_paid) {
            return collect();
        }

        // static variable so we can 'cache' this value during request processing
        static $avail_methods;

        // if its not set, lets grab 'em
        if (! isset($avail_methods)) {
            // start w/ an empty array (no options yet)
            $avail_methods = [];

            // tiered options
            if (sys_get('shipping_handler') === 'tiered') {
                // loop through available methods
                foreach (ShippingMethod::getByGeography($this->shipstate, $this->shipcountry) as $method) {
                    $avail_methods[] = (object) [
                        'courier' => null,
                        'code' => null,
                        'name' => $method->name,
                        'title' => $method->name,
                        'cost' => money(ShippingMethod::getShippingCost($method->id, $this->subtotal_shippable))
                            ->toCurrency($this->currency_code)
                            ->getAmount(),
                        'shipping_method_id' => $method->id,
                        'is_default' => $method->is_default,
                    ];
                }

                // rated options (CACHED RESULTS)
            } elseif (sys_get('shipping_handler') === 'courier') {
                // if no shipping necessary, return no available options
                if ($this->total_weight === 0.0) {
                    return collect($avail_methods);
                }

                // cache key (rated_weight_city_prov_postal_country)
                $cache_key = implode(' ', [
                    sys_get('shipping_handler'),
                    $this->currency_code,
                    $this->total_weight,
                    $this->shipcity,
                    $this->shipstate,
                    $this->shipzip,
                    $this->shipcountry,
                ]);

                // length of time to save cache (12hrs)
                $minutes = now()->addHours(12);

                // reference to THIS order
                $order = $this;

                // return a cached set of results
                $avail_methods = Cache::tags('shipping')->remember($cache_key, $minutes, function () use ($order) {
                    $rates = collect();

                    $options = new \Ds\Domain\Commerce\Shipping\ShipmentOptions($order);

                    if (sys_get('shipping_canadapost_enabled')) {
                        $rates = $rates->merge(app(\Ds\Domain\Commerce\Shipping\Carriers\CanadaPost::class)->getRates($options));
                    }

                    if (sys_get('shipping_fedex_enabled')) {
                        $rates = $rates->merge(app(\Ds\Domain\Commerce\Shipping\Carriers\FedEx::class)->getRates($options));
                    }

                    if (sys_get('shipping_ups_enabled')) {
                        $rates = $rates->merge(app(\Ds\Domain\Commerce\Shipping\Carriers\UPS::class)->getRates($options));
                    }

                    if (sys_get('shipping_usps_enabled')) {
                        $rates = $rates->merge(app(\Ds\Domain\Commerce\Shipping\Carriers\USPS::class)->getRates($options));
                    }

                    return $rates->map(function ($rate) {
                        $rate = $rate->toCurrency($this->currency_code);

                        return (object) [
                            'courier' => $rate->carrier,
                            'code' => $rate->code,
                            'name' => $rate->name,
                            'title' => $rate->title,
                            'cost' => $rate->amount,
                            'shipping_method_id' => null,
                            'is_default' => false,
                        ];
                    });
                });

                // if there are no results, clear cache (ex: canada post returns nothing because of an error)
                if ($avail_methods === null || count($avail_methods) === 0) {
                    Cache::forget($cache_key);
                }
            }
        }

        // return results
        return collect($avail_methods);
    }

    /**
     * Attribute Mask: dpo_status_message
     *
     * @return string|null
     */
    public function getDpoStatusMessageAttribute()
    {
        if ($this->alt_contact_id) {
            return null;
        }

        // all key fiels are blank, return error
        if (trim($this->billing_first_name) === ''
            && trim($this->billing_last_name) === ''
            && trim($this->billingzip) === ''
            && trim($this->billingemail) === '') {
            return 'There is not enough user data to link to a donor. Try using the Anonymous Donor setting in Administration.';
        }

        // email blank, and one of first/last/email is also blank
        if (trim($this->billingemail) === ''
            && (trim($this->billing_first_name) === ''
                || trim($this->billing_last_name) === ''
                || trim($this->billingzip) === '')
            ) {
            return 'There is not enough user data to link to a donor. Double check the billing first name, last name and ZIP/Postal code.';
        }

        if (strlen($this->billingzip) > 10) {
            return 'The billing ZIP/Postal Code (' . $this->billingzip . ') may be incorrectly formatted.';
        }

        if (strlen($this->billingstate) > 2) {
            return 'The billing State/Province (' . $this->billingstate . ') may be incorrectly formatted.';
        }

        if (! dpo_is_connected()) {
            return 'Your DP username and password are not valid. Please update your DP credentials in Settings.';
        }
    }

    /**
     * Attribute Mask: referral_source_restricted
     *
     * Limit the value of referral_source to match the
     * configuration of referral_source options.
     *
     * @return string|null
     */
    public function getReferralSourceRestrictedAttribute()
    {
        if (empty($this->referral_source)) {
            return null;
        }

        $options = explode(',', sys_get('referral_sources_options'));

        if (! in_array($this->referral_source, $options)) {
            return 'Other';
        }

        return $this->referral_source;
    }

    /**
     * Attribute Mask: is_view_only
     *
     * Can this order be edited?
     *
     * @return bool
     */
    public function getIsViewOnlyAttribute()
    {
        // must be a paid order
        if (! $this->confirmationdatetime) {
            return true;
        }

        // must be logged in w/ permission
        if (! $this->userCan('edit')) {
            return true;
        }

        // must not be deleted
        if ($this->trashed()) {
            return true;
        }

        return false;
    }

    /**
     * Attribute Mask: is_trashable
     *
     * Can this order be trashed?
     *
     * @return bool
     */
    public function getIsTrashableAttribute()
    {
        return count($this->trashable_messages) === 0;
    }

    /**
     * Attribute Mask: trashable_messages
     *
     * Produce an array of messages that reflect the
     * all the reasons an order may not be deleted.
     * Primary use case is to reduce support team
     * time/effort in helping people figure out
     * when they can/can't delete orders.
     *
     * Orders can only be trashed if:
     * - they are a test order
     * - OR -
     * - they were entered through POS with amount over 0, AND
     * - they have no electronic payments, AND
     * - have no RPP's with payments, AND
     * - have no refunds of any kind, AND
     * - have no shippable items, AND
     * - have no views on edownloads
     *
     * @return array
     */
    public function getTrashableMessagesAttribute()
    {
        $messages = [];

        if ($this->is_test) {
            return [];
        }

        if (! $this->is_pos && $this->totalamount > 0) {
            $messages[] = 'This contribution was not data entered in POS.';
        }

        $payments_with_live_payments = $this->payments->reject(function ($payment) {
            return in_array($payment->type, ['unknown', 'cash', 'cheque']);
        });

        if ($payments_with_live_payments->count() > 0) {
            $messages[] = 'This contribution has live electronic payments associated with it and should be refunded, not deleted.';
        }

        // payments w/ refunds
        $payments_with_refunds = $payments_with_live_payments->filter(function ($payment) {
            return $payment->refunds()->count() > 0;
        })->count();

        if ($payments_with_refunds > 0) {
            $messages[] = "This contribution has ($payments_with_refunds) refunded payment(s).";
        }

        // RPPs with transactions
        $rpps_with_txns = $this->items->filter(function ($item) {
            return ($item->recurringPaymentProfile) ? $item->recurringPaymentProfile->transactions()->count() > 0 : false;
        })->count();

        if ($rpps_with_txns > 0) {
            $messages[] = "This contribution has ($rpps_with_txns) recurring profile(s) that have already accepted payment(s).";
        }

        // shippable items that have shipped
        if ($this->shippable_items > 0 && $this->is_complete) {
            $messages[] = 'This contribution has (' . $this->shippable_items . ') shippable item(s) and the contribution has been completed.';
        }

        // accessed downloads
        $accessed_downloads = DB::table('productorderitemfiles')
            ->whereIn('orderitemid', $this->items->pluck('id'))
            ->where('accessed', '>', 0)
            ->count();

        if ($accessed_downloads > 0) {
            $messages[] = "This contribution contains ($accessed_downloads) digital download(s) that have already been accessed by the customer.";
        }

        return $messages;
    }

    /**
     * Create a cart for POS to process.
     *
     * @return self
     */
    public static function newPOSOrder()
    {
        $order = new self;

        $order->client_uuid = strtoupper(uuid());
        $order->client_ip = request()->ip();
        $order->client_browser = request()->server('HTTP_USER_AGENT');
        $order->started_at = new Carbon;
        $order->currency_code = sys_get('dpo_currency');
        $order->ordered_at = new Carbon;
        $order->source = (trim(sys_get('pos_sources'))) ? explode(',', sys_get('pos_sources'))[0] : null;
        $order->billingcountry = sys_get('default_country');
        $order->shipcountry = sys_get('default_country');
        $order->is_pos = true;
        $order->is_anonymous = true;
        $order->tax_address1 = (sys_get('pos_tax_address1')) ? sys_get('pos_tax_address1') : null;
        $order->tax_address2 = (sys_get('pos_tax_address2')) ? sys_get('pos_tax_address2') : null;
        $order->tax_city = (sys_get('pos_tax_city')) ? sys_get('pos_tax_city') : null;
        $order->tax_state = (sys_get('pos_tax_state')) ? sys_get('pos_tax_state') : null;
        $order->tax_zip = (sys_get('pos_tax_zip')) ? sys_get('pos_tax_zip') : null;
        $order->tax_country = (sys_get('pos_tax_country')) ? sys_get('pos_tax_country') : null;
        $order->tax_receipt_type = sys_get('tax_receipt_type');
        $order->account_type_id = \Ds\Models\AccountType::default()->first()->id;
        $order->save();

        return $order;
    }

    /**
     * Add an item to the cart.
     *
     * @param array $data
     * @return \Ds\Models\OrderItem
     *
     * @throws \Ds\Domain\Shared\Exceptions\MessageException
     */
    public function addItem(array $data): OrderItem
    {
        return app(OrderAddItemService::class)->store($data, $this);
    }

    /**
     * Add a sponsorship to the cart.
     *
     * @param array $data
     * @return \Ds\Models\OrderItem
     *
     * @throws \Ds\Domain\Shared\Exceptions\MessageException
     */
    public function addSponsorship(array $data): OrderItem
    {
        if ($this->is_paid) {
            throw new MessageException("Unable to add items after a contribution has been paid (Order: $this->client_uuid)");
        }

        // set defaults
        $data = [
            'sponsorship_id' => $data['sponsorship_id'] ?? null,
            'payment_option_id' => $data['payment_option_id'] ?? null,
            'payment_option_amount' => $data['payment_option_amount'] ?? 0,
            'initial_charge' => $data['initial_charge'] ?? false,
        ];

        // validate input
        $validator = app('validator')->make($data, [
            'sponsorship_id' => 'required|exists:' . Sponsorship::class . ',id,is_deleted,0',
            'payment_option_id' => 'required|exists:payment_option,id,is_deleted,0',
            'payment_option_amount' => 'numeric|min:0',
            'initial_charge' => 'boolean',
        ], [
            'sponsorship_id.required' => 'No sponsorship selected.',
            'sponsorship_id.exists' => 'Error retrieving sponsorship information.',
            'payment_option_id.required' => 'No pamyent method selected.',
            'payment_option_id.exists' => 'Error retrieving payment method information.',
        ]);

        if ($validator->fails()) {
            throw new MessageException($validator->errors()->first());
        }

        $sponsorship = Sponsorship::find($data['sponsorship_id']);

        if (! empty(sys_get('sponsorship_max_sponsors')) && $sponsorship->sponsor_count >= sys_get('sponsorship_max_sponsors')) {
            throw new MessageException("{$sponsorship->display_name} has already been sponsored.");
        }

        // get the payment option
        $payment_option = PaymentOption::find($data['payment_option_id']);

        // setup the item
        $item = new \Ds\Models\OrderItem;
        $item->productorderid = $this->id;
        $item->sponsorship_id = $data['sponsorship_id'];
        $item->price = $data['payment_option_amount'] ?: $payment_option->amount;
        $item->qty = 1;

        if ($payment_option->is_recurring) {
            $item->recurring_day = $payment_option->recurring_day;
            $item->recurring_day_of_week = $payment_option->recurring_day_of_week;
            $item->recurring_frequency = $payment_option->recurring_frequency;
            $item->recurring_with_dpo = $payment_option->recurring_with_dpo;
            $item->recurring_amount = $item->price;
            $item->recurring_with_initial_charge = $data['initial_charge'] ? 1 : 0;

            if (sys_get('rpp_default_type') === 'natural') {
                $item->recurring_with_initial_charge = true;

                if ($item->recurring_frequency == 'weekly' || $item->recurring_frequency == 'biweekly') {
                    $item->recurring_day_of_week = fromLocal('today')->dayOfWeek;
                } else {
                    $item->recurring_day = fromLocal('today')->day;
                }
            }

            if (! $item->recurring_with_initial_charge) {
                $item->price = 0;
            }
        }

        $item->save();

        $this->load('items');
        $this->calculate();

        return $item;
    }

    /**
     * Remove an item from the cart.
     *
     * @param int $order_item_id
     * @return \Ds\Models\Order
     *
     * @throws \Ds\Domain\Shared\Exceptions\MessageException
     */
    public function removeItem($order_item_id): Order
    {
        // be sure orders aren't complete
        if ($this->confirmationdatetime) {
            throw new MessageException('Cannot remove items from a completed contribution.');
        }

        $item = $this->items()->where('id', $order_item_id)->firstOrFail();

        if ($item->locked_to_item_id) {
            throw new MessageException('Cannot remove items that are part of a bundle.');
        }

        $this->items()->where('id', $order_item_id)->delete();
        $this->items()->where('locked_to_item_id', $order_item_id)->delete();

        $this->loadLoaded('items');

        // need to manually caculate since using the delete method
        // on the builders prevents the observer from updating the model
        $this->calculate();
        $this->reapplyPromos();

        // return the order
        return $this;
    }

    /**
     * Validate the order.
     *
     * @return bool
     */
    public function validate(array $validations = null)
    {
        if ($validations) {
            $validator = new \Ds\OrderValidator($this);

            foreach ($validations as $validation) {
                $validator->validate($validation);
            }

            return true;
        }

        // if the item requires shipping, but no shipping address is provided (and NOT is_pos)

        // if download_items > 0 but no billingemail provided, fail

        // if weight > 0 but no shipping address or method, fail

        // if billing address is blank and NOT is_pos, fail

        // if recurring payment and no account, fail

        // if force account and no account, fail

        // if no items, fail

        // return true (its valid)
        return true;
    }

    /**
     * Refresh cart/order totals based on items in the cart, shipping, promos, etc.
     *
     * @return \Ds\Models\Order
     */
    public function calculate()
    {
        if ($this->is_paid) {
            return $this;
        }

        $this->load('items.variant.product');

        if ($this->fundraisingForm) {
            $this->dp_sync_order = $this->fundraisingForm->dp_enabled;
        }

        $this->subtotal = collect($this->items)->sum('total');
        $this->taxtotal = 0.0;
        $this->shipping_amount = 0.0;
        $this->totalamount = 0.0;

        $this->updateAggregates();

        $this->calculateShipping();
        $this->calculateTax();
        $this->calculateDcc();

        // update total amount
        // NOTE: rounding to 2 decimal places here doesn't seem to make a difference.
        // The DB is set to a higher accuracy so more decimal places are filled in when it saved to the db
        $this->totalamount = round($this->subtotal + $this->taxtotal + $this->shipping_amount + $this->dcc_total_amount, 2);

        $this->save();

        return $this;
    }

    /**
     * Update all the aggregate counts on the order
     *
     * @return Order
     */
    public function updateAggregates()
    {
        $this->total_items = collect($this->items)->count();
        $this->total_qty = collect($this->items)->sum('qty');

        $this->total_weight = collect($this->items)
            ->filter(function ($item) {
                return $item->requires_shipping && ! $item->has_free_shipping;
            })->sum(function ($item) {
                return ($item->variant->weight ?? 0) * $item->qty;
            });

        // this calculation of the savings doesn't include any potential tax savings
        $this->total_savings = collect($this->items)
            ->reject(function ($item) {
                return $item->variant->is_donation ?? true;
            })->sum(function ($item) {
                return ($item->original_price * $item->qty) - $item->total;
            });

        $this->recurring_items = collect($this->items)
            ->filter(function ($item) {
                return $item->recurring_amount > 0;
            })->count();

        $this->download_items = collect($this->items)
            ->filter(function ($item) {
                return $item->variant->file ?? false;
            })->count();

        $this->shippable_items = collect($this->items)
            ->filter(function ($item) {
                return $item->requires_shipping;
            })->count();

        return $this;
    }

    /**
     * Calculate shipping on this order
     *
     * @return \Ds\Models\Order
     */
    protected function calculateShipping()
    {
        // if we don't have valid shipping info,
        // we can't calculate shipping
        if (! feature('shipping') || ! $this->is_shipping_address_valid) {
            $this->shipping_amount = 0;
            $this->shipping_method_id = null;
            $this->courier_method = null;

            return $this;
        }

        $free_shipping_promocodes = $this->promoCodes->where('is_free_shipping', true)->count();

        // if there are free shipping promocodes
        // ensure that is_free_shipping is set
        // on the order
        if ($free_shipping_promocodes > 0) {
            $this->is_free_shipping = true;
        }

        // if there are shippable items but none of
        // them impact the cost of shipping, set
        // the order.is_free_shipping flag to TRUE
        if (! $free_shipping_promocodes && $this->shippable_items > 0) {
            $shippable_items_that_have_a_shipping_cost = collect($this->items)->filter(function ($item) {
                return $item->requires_shipping && ! $item->has_free_shipping;
            })->count();

            $this->is_free_shipping = $shippable_items_that_have_a_shipping_cost === 0;
            if ($this->is_free_shipping) {
                $this->courier_method = null;
            }
        }

        // FREE shipping (promocode or no cost-impacting items)
        // OR if there are no shippable items
        if ($this->is_free_shipping
                || $this->shippable_items < 1) {
            // do nothing :)
            $this->shipping_amount = 0;
            $this->shipping_method_id = null;
            $this->courier_method = null;

        // we're using flat-rate shipping
        } elseif (sys_get('shipping_handler') === 'tiered') {
            // make sure courier identifier is null
            $this->courier_method = null;

            $shippingMethod = $this->shippingMethod;

            // revalidate the shipping region and country in case they've changed
            if ($shippingMethod && $shippingMethod->regions && ! in_array($this->shipstate, $shippingMethod->regions)) {
                $shippingMethod = null;
            }

            if ($shippingMethod && $shippingMethod->countries && ! in_array($this->shipcountry, $shippingMethod->countries)) {
                $shippingMethod = null;
            }

            // set default shipping method
            if (empty($shippingMethod)) {
                $shippingMethod = \Ds\Models\ShippingMethod::getByGeography($this->shipstate, $this->shipcountry)
                    ->sortByDesc('is_default')
                    ->first();

                $this->shipping_method_id = $shippingMethod->id ?? null;
            }

            // set amount
            if ($this->shipping_method_id) {
                $this->shipping_amount = ShippingMethod::getShippingCost($this->shipping_method_id, $this->subtotal_shippable);
            } else {
                $this->shipping_amount = 0;
            }

            // if we're using rated shipping
        } elseif (sys_get('shipping_handler') === 'courier') {
            // make sure flat-rate identifier is null
            $this->shipping_method_id = null;

            // if there is no courier method set
            // OR the currently set method is no longer available (maybe address changed... maybe weight changed)
            if ($this->courier_method === null
                || ! $this->isAvailableCourierMethod($this->courier_method)) {
                // default to cheapest available shipping method
                $this->courier_method = data_get($this->getCheapestAvailableCourierMethod(), 'title');
            }

            // set/refresh the shipping rate
            if ($this->courier_method) {
                $this->shipping_amount = $this->getCourierMethodCost($this->courier_method);
            } else {
                $this->shipping_amount = 0;
            }
        }

        // return a reference to $this order
        return $this;
    }

    /**
     * Calculate taxes on this order
     *
     * @return void
     */
    protected function calculateTax()
    {
        // bail if no tax feature enabled
        if (! feature('taxes')) {
            return;
        }

        // if TaxCloud integration is enabled, use
        // TaxCloudRepository to apply taxes to the order
        if (sys_get('taxcloud_api_key')) {
            return app(TaxCloudRepository::class)->applyTaxes($this);
        }

        // TO-DO - This should not happen everytime we calculate taxes.
        // It should only recalculate if the region changes.

        // calculate tax on the items vs shipping
        $total_tax_items = 0;
        $total_tax_shipping = 0;

        // loop over each item
        foreach ($this->items as $item) {
            // apply new taxes
            $item->applyTaxes();
        }

        // tax total on items
        $total_tax_items = (float) db_var(
            'SELECT SUM(it.amount) AS total_amount
                FROM producttax t
                INNER JOIN productorderitemtax it ON it.taxid = t.id
                INNER JOIN productorderitem i ON i.id = it.orderitemid
                WHERE i.productorderid = %d
                GROUP BY i.productorderid',
            $this->id
        );

        // Apply taxes on shipping
        // if we are to charge tax on shipping
        if (sys_get('shipping_taxes_apply') == 1) {
            // sum tax rates (of unique codes)
            $tax_rate_sum = DB::select(
                DB::raw('SELECT SUM(t1.rate) AS total_rate
                    FROM (SELECT DISTINCT t.id, t.code, t.rate
                        FROM producttax t
                        INNER JOIN productorderitemtax it ON it.taxid = t.id
                        INNER JOIN productorderitem i ON i.id = it.orderitemid
                        WHERE i.productorderid = :order_id
                        GROUP BY t.code) t1'),
                [
                    'order_id' => $this->id,
                ]
            );

            // if a rate was returned, charge it
            if ($tax_rate_sum) {
                $total_tax_shipping = $this->shipping_amount * ($tax_rate_sum[0]->total_rate / 100);
            }
        }

        // total tax
        $this->taxtotal = round($total_tax_items + $total_tax_shipping, 2);
    }

    /**
     * Calculate "donor covers costs" on this order
     */
    protected function calculateDcc()
    {
        if (! sys_get('dcc_enabled')) {
            return;
        }

        if (empty($this->dcc_enabled_by_customer)) {
            DB::table('productorderitem')
                ->where('productorderid', $this->id)
                ->update([
                    'dcc_amount' => 0,
                    'dcc_recurring_amount' => 0,
                ]);
        } else {
            $nonRecurringFlatFee = 0;
            $nonRecurringEligibleItems = $this->items->filter(function ($item) {
                return $item->dcc_eligible && ! $this->is_recurring;
            });

            if ($nonRecurringEligibleItems->isNotEmpty()) {
                $nonRecurringFlatFee = $this->dcc_per_order_amount / $nonRecurringEligibleItems->count();
            }

            foreach ($this->items as $item) {
                $flatFee = $item->is_recurring ? $this->dcc_per_order_amount : $nonRecurringFlatFee;

                $dccAmount = 0;
                $dccRecurringAmount = 0;

                if ($item->dcc_eligible && $item->total > 0) {
                    $dccAmount = sys_get('dcc_ai_is_enabled')
                        ? app(DonorCoversCostsService::class)->getCost($item->total, $item->order->dcc_type)
                        : round($flatFee + ($item->total * $this->dcc_rate / 100), 2);
                }

                if ($item->dcc_eligible && $item->recurring_amount > 0) {
                    $dccRecurringAmount = sys_get('dcc_ai_is_enabled')
                    ? app(DonorCoversCostsService::class)->getCost($item->recurring_amount, $item->order->dcc_type)
                    : round($flatFee + ($item->recurring_amount * $this->dcc_rate / 100), 2);
                }

                DB::table('productorderitem')
                    ->where('id', $item->id)
                    ->update([
                        'dcc_amount' => $dccAmount,
                        'dcc_recurring_amount' => $dccRecurringAmount,
                    ]);
            }
        }

        $this->load('items');
        $this->dcc_total_amount = round($this->items->sum('dcc_amount'), 2);
    }

    /**
     * Get the tax lines for the order.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getTaxLinesAttribute()
    {
        $lines = collect(DB::select(
            '
            SELECT t.code, t.rate, SUM(it.amount) as price
            FROM producttax t
            INNER JOIN productorderitemtax it ON it.taxid = t.id
            INNER JOIN productorderitem i ON i.id = it.orderitemid
            WHERE i.productorderid = ?
            GROUP BY t.id, t.code, t.rate',
            [$this->id]
        ));

        if (sys_get('shipping_taxes_apply')) {
            $lines->each(function ($line) {
                $line->price += $this->shipping_amount * ($line->rate / 100);
            });
        }

        return $lines->values();
    }

    /**
     * Check to make sure that the shipping method selected is valid
     * for the chosen region. If it's not valid, set a valid choice.
     *
     * @return void
     */
    public function verifyFlatRateShipping()
    {
        // if the method is not valid
        if (! $this->isAvailableShippingMethod($this->shipping_method_id)) {
            // set a valid method (the first method in the available methods list)
            if ($this->available_shipping_methods->count() > 0) {
                $this->shipping_method_id = $this->available_shipping_methods->first()->shipping_method_id;
            } else {
                $this->shipping_method_id = null;
            }

            // push update to DB
            $this->save();

            // recalculate & save
            $this->calculate();
        }
    }

    /**
     * Check to make sure the chosen shipping method is available in the
     * chosen region (state/country)
     *
     * @param int $shipping_method_id
     * @return bool
     */
    public function isAvailableShippingMethod($shipping_method_id)
    {
        // does the shipping_method_id passed in exist in the list of available_shipping_methods?
        return $this->available_shipping_methods
            ->where('shipping_method_id', $shipping_method_id)
            ->count() > 0;
    }

    /**
     * Check to see if a courier method is an available method on this order.
     *
     * @param string $method_name The name of the shipping method from the shipper (ups, usps, etc) (Example; 'Xpress Post')
     * @return bool
     */
    public function isAvailableCourierMethod($method_name)
    {
        // does the shipping_method_id passed in exist in the list of available_shipping_methods?
        return $this->available_shipping_methods
            ->filter(function ($item) {
                return $item->courier != null;
            })->where('title', $method_name)
            ->count() > 0;
    }

    /**
     * Get the cost of shipping based on a specific method.
     *
     * @return float
     */
    public function getCourierMethodCost($method_name)
    {
        // find the matching courier method
        $method = $this->available_shipping_methods
            ->filter(function ($item) {
                return $item->courier != null;
            })->where('title', $method_name)
            ->first();

        // if the method exists, return the cost, otherwise 0
        return ($method) ? $method->cost : 0;
    }

    /**
     * Return the cheapest courier method.
     *
     * @return object
     */
    public function getCheapestAvailableCourierMethod()
    {
        // assume its the first method
        $cheapest = $this->available_shipping_methods->first();

        // loop over all available and find the cheapest
        foreach ($this->available_shipping_methods as $method) {
            if ($method->cost < $cheapest->cost) {
                $cheapest = $method;
            }
        }

        // return the cheapest
        return $cheapest;
    }

    /**
     * Does this order contain any recurring GC payments?
     *
     * @return bool
     */
    public function hasRecurringItemsInDs()
    {
        if (sys_get('rpp_donorperfect')) {
            return false;
        }

        return $this->recurring_items > 0;
    }

    /**
     * Create a member from the billing data in this order.
     *
     * @return \Ds\Models\Member|null
     */
    public function createMember($password = null, $force_create_member = false)
    {
        if (! $force_create_member && $this->member) {
            return $this->member;
        }

        $member = \Ds\Models\Member::createFromOrder($this, $password, $force_create_member);

        if ($member) {
            // link the order to the new member
            $this->member_id = $member->id;

            // save the order
            $this->save();

            // apply account to payments
            $this->payments()->update([
                'source_account_id' => $member->id,
            ]);

            // reload the member relationship
            $this->load('member', 'member.accountType', 'member.groups');

            // return the new member
            return $member;
        }

        return null;
    }

    /**
     * Issue a tax receipt.
     *
     * @param bool $auto_notify whether or not to instantly notify the owner of the tax receipt
     * @return \Ds\Models\TaxReceipt
     */
    public function issueTaxReceipt($auto_notify = false)
    {
        // create and return a new tax receipt
        return \Ds\Models\TaxReceipt::createFromOrder($this->id, $auto_notify);
    }

    /**
     * Return all memberships purchased in this order
     *
     * @return \Illuminate\Support\Collection
     */
    public function memberships()
    {
        $membership_ids = [];
        foreach ($this->items as $item) {
            if ($item->variant && $item->variant->membership_id) {
                array_push($membership_ids, $item->variant->membership_id);
            }
        }

        return \Ds\Models\Membership::whereIn('id', $membership_ids)->get();
    }

    /**
     * Refund the entire order.
     *
     * @return \Ds\Models\Order
     */
    public function refund($amount = null)
    {
        // assume we are refunding the total amount unless otherwise specified
        if (! isset($amount)) {
            $amount = $this->totalamount;
        }

        $amount = min($amount, $this->totalamount - $this->refunded_amt);

        // was the order already refunded?
        if ($this->is_refunded) {
            throw new MessageException('Contribution #' . $this->invoicenumber . ' already fully refunded.');
        }

        // is there an amount to refund?
        if ($this->totalamount == 0 || $amount == 0) {
            throw new MessageException('Refund failed for Contribution #' . $this->invoicenumber . '. There is no refundable amount.');
        }

        if (in_array($this->payment_type, ['eft', 'cash', 'check', 'other'])) {
            $res = new TransactionResponse(new PaymentProvider, [
                'completed' => true,
                'response' => 'succeeded',
                'response_text' => 'Refund has been approved.',
                'transaction_id' => Str::random(24),
            ]);
        } else {
            try {
                $res = $this->paymentProvider->refundCharge($this->confirmationnumber, $amount, $amount == $this->totalamount, $this->paymentMethod);
            } catch (GatewayException $e) {
                if (count($this->successfulPayments)) {
                    $res = $e->getResponse();

                    $refund = new Refund;
                    $refund->status = 'failed';
                    $refund->amount = $amount;
                    $refund->currency = $this->currency_code;
                    $refund->reason = 'requested_by_customer';
                    $refund->refunded_by_id = user('id') ?? 1;

                    if ($res) {
                        $refund->reference_number = $res->getTransactionId();
                        $refund->refund_audit_log = 'json:' . json_encode($res);
                    } else {
                        $refund->refund_audit_log = $e->getMessage();
                    }

                    $this->successfulPayments->first()->refunds()->save($refund);
                }

                throw new MessageException("Refund failed for Contribution #$this->invoicenumber ($this->confirmationnumber). $e");
            }
        }

        // update the order
        $this->refunded_at = now();
        $this->refunded_amt = $this->refunded_amt + $amount;
        $this->refunded_auth = $res->getTransactionId();
        $this->refunded_by = user('id');
        $this->save();

        if (count($this->successfulPayments)) {
            $refund = new Refund;
            $refund->status = $res->getResponse() ?? '';
            $refund->reference_number = $res->getTransactionId();
            $refund->amount = $amount;
            $refund->currency = $this->currency_code;
            $refund->reason = 'requested_by_customer';
            $refund->refunded_by_id = $this->refunded_by ?? 1;
            $refund->refund_audit_log = 'json:' . json_encode($res);
            $refund->created_at = $this->refunded_at;
            $refund->updated_at = $this->refunded_at;

            $this->successfulPayments->first()->refunds()->save($refund);
        }

        // void/delete any related tax receipt
        if ($this->taxReceipt) {
            $this->taxReceipt->void();
        }

        // fire queued event listeners
        event(new \Ds\Events\OrderWasRefunded($this));

        // return the order
        return $this;
    }

    /**
     * Set the member and populate their data into the cart.
     *
     * @param \Ds\Models\Member|int $member
     * @return \Ds\Models\Order
     */
    public function populateMember($member)
    {
        // if this order has already been paid for, bail
        if ($this->is_paid) {
            throw new MessageException("Cannot modifed an contribution that's already been paid for.");
        }

        // if the $member argument is not a Ds\Models\Member
        if (! is_a($member, Member::class)) {
            // try finding a member
            $member = Member::find($member);

            // if no member found, bail
            if (! $member) {
                throw new MessageException('Cannot populate supporter into contribution. No supporter found with id ' . $member . '.');
            }
        }

        // set the member
        $this->member_id = $member->id;

        // update the order with the member data
        $this->account_type_id = $member->account_type_id;
        $this->shipping_organization_name = $member->ship_organization_name;
        $this->shipping_title = $member->ship_title;
        $this->shipping_first_name = $member->ship_first_name;
        $this->shipping_last_name = $member->ship_last_name;
        $this->shipemail = $member->ship_email;
        $this->shipaddress1 = $member->ship_address_01;
        $this->shipaddress2 = $member->ship_address_02;
        $this->shipcity = $member->ship_city;
        $this->shipstate = $member->ship_state;
        $this->shipzip = $member->ship_zip;
        $this->shipcountry = $member->ship_country;
        $this->shipphone = $member->ship_phone;
        $this->billing_title = $member->bill_title;
        $this->billing_first_name = $member->bill_first_name;
        $this->billing_last_name = $member->bill_last_name;
        $this->billing_organization_name = $member->bill_organization_name;
        $this->billingemail = $member->bill_email;
        $this->billingaddress1 = $member->bill_address_01;
        $this->billingaddress2 = $member->bill_address_02;
        $this->billingcity = $member->bill_city;
        $this->billingstate = $member->bill_state;
        $this->billingzip = $member->bill_zip;
        $this->billingcountry = $member->bill_country;
        $this->billingphone = $member->bill_phone;
        $this->referral_source = $member->referral_source;

        // save
        $this->save();

        // reapply per-person promos
        $this->revalidatePerAccountPromos();

        // apply membership promos
        $this->member->applyMembershipPromocodes($this);

        // apply account to payments
        $this->payments()->update([
            'source_account_id' => $member->id,
        ]);

        // return the order
        return $this;
    }

    /**
     * Clear member data.
     *
     * @return \Ds\Models\Order
     */
    public function unpopulateMember()
    {
        // if this order has already been paid for, bail
        if ($this->is_paid) {
            throw new MessageException("Cannot modifed an contribution that's already been paid for.");
        }

        // set the member
        $this->member_id = null;
        $this->shipping_first_name = null;
        $this->shipping_last_name = null;
        $this->shipemail = null;
        $this->shipaddress1 = null;
        $this->shipaddress2 = null;
        $this->shipcity = null;
        $this->shipstate = null;
        $this->shipzip = null;
        $this->shipcountry = sys_get('default_country');
        $this->shipphone = null;
        $this->billing_first_name = null;
        $this->billing_last_name = null;
        $this->billingemail = null;
        $this->billingaddress1 = null;
        $this->billingaddress2 = null;
        $this->billingcity = null;
        $this->billingstate = null;
        $this->billingzip = null;
        $this->billingcountry = sys_get('default_country');
        $this->billingphone = null;
        $this->referral_source = null;

        // if pos, set tax regions
        if ($this->is_pos) {
            $this->tax_address1 = sys_get('pos_tax_address1');
            $this->tax_address2 = sys_get('pos_tax_address2');
            $this->tax_city = sys_get('pos_tax_city');
            $this->tax_state = sys_get('pos_tax_state');
            $this->tax_zip = sys_get('pos_tax_zip');
            $this->tax_country = sys_get('pos_tax_country');
        }

        // save
        $this->save();

        // remove account from payments
        $this->payments()->update([
            'source_account_id' => null,
        ]);

        // remove membership promocodes
        // (this could be smarter)
        $this->clearPromos();

        // return the order
        return $this;
    }

    /**
     * Lock the order so no other payment functions start
     * while payments are being processed.
     *
     * @return \Ds\Models\Order
     */
    public function lock()
    {
        // lock & save
        $this->payment_lock_at = now();
        $this->save();

        // return the order
        return $this;
    }

    /**
     * Unlock the order.
     *
     * @return \Ds\Models\Order
     */
    public function unlock()
    {
        // lock & save
        $this->payment_lock_at = null;
        $this->save();

        // return the order
        return $this;
    }

    /**
     * Process the payment on the order.
     *
     * @param array $opts
     */
    public function processPayment($opts)
    {
        if ($this->is_paid) {
            throw new MessageException('This contribution has already been charged.');
        }

        // validate payment_type
        if (! $opts['payment_type'] || ! in_array($opts['payment_type'], $this->payment_types)) {
            throw new MessageException("Contribution cannot be completed. '" . $opts['payment_type'] . "' is not a valid payment type.");
        }

        // if recurring items exist and no member is chosen,
        // create member from order
        if ($this->recurring_items > 0 && ! $this->member_id) {
            $this->createMember();
        }

        // force payment_at
        if (! $opts['payment_at']) {
            $opts['payment_at'] = now();
        }

        // force is_processed
        if (! $opts['is_complete']) {
            $opts['is_complete'] = false;
        }

        // if free
        if ($opts['payment_type'] == 'free') {
            // nothing to do for free orders

        // if cash
        } elseif ($opts['payment_type'] == 'cash') {
            // validate
            if (round((float) ($opts['cash_received']), 2) < round($this->totalamount, 2)) {
                throw new MessageException("Contribution cannot be completed. Insufficient cash payment value of '" . $opts['cash_received'] . "' on a contribution totaling $" . number_format($this->totalamount, 2) . '.');
            }

            // set appropriate params
            $this->payment_provider_id = PaymentProvider::getOfflineProviderId();
            $this->cash_received = round((float) ($opts['cash_received']), 2);
            $this->cash_change = $opts['cash_change'];

        // if check
        } elseif ($opts['payment_type'] == 'check') {
            // validate
            if (round((float) ($opts['check_amt']), 2) < round($this->totalamount, 2)) {
                throw new MessageException("Contribution cannot be completed. Insufficient check payment value of '" . $opts['check_amt'] . "' on a contribution totaling $" . number_format($this->totalamount, 2) . '.');
            }

            // set appropriate params
            $this->payment_provider_id = PaymentProvider::getOfflineProviderId();
            $this->check_number = $opts['check_number'];
            $this->check_date = $opts['check_date'];
            $this->check_amt = round((float) ($opts['check_amt']), 2);

        // if other
        } elseif ($opts['payment_type'] == 'other') {
            // set appropriate params
            $this->payment_provider_id = PaymentProvider::getOfflineProviderId();
            $this->payment_other_reference = $opts['payment_other_reference'];
            $this->payment_other_note = $opts['payment_other_note'];

        // if unsupported
        } else {
            throw new MessageException('Unsupported payment type.');
        }

        // unviersal order updates (regardless of payment type)
        $this->createddatetime = now();
        $this->ordered_at = fromUtc($this->ordered_at ?? $this->createddatetime);
        $this->invoicenumber = $this->client_uuid;
        $this->confirmationdatetime = $opts['payment_at'];
        $this->payment_type = $opts['payment_type'];
        $this->is_processed = true;

        // save order
        $this->save();

        // unlock
        $this->unlock();

        // create payment
        app(PaymentService::class)->createPaymentFromOrder($this);

        // after processed
        $this->afterProcessed();

        app('activitron')->increment('Site.payments.success');
    }

    /**
     * USED IN ONE-PAGE CHECKOUT
     * (could this be converted to an eloquent event of some kind?)
     *
     * Called anytime an order is finished being processed successfully.
     * The order is fully populated (including any related member) and approved.
     */
    public function afterProcessed()
    {
        // save a copy of all the original ITEM data
        // on the order (it can be edited after the
        // fact - we don't want to lose the original
        // data submitted)
        $this->saveOriginalData();

        // initialize recurring payments
        $this->initializeRecurringPayments();

        // grant access to downloads
        $this->grantDownloads();

        // if no member was created, create one
        $this->createMember();

        // fire queued event listeners (webhooks, emails, pdfs)
        event(new \Ds\Events\OrderWasCompleted($this));
    }

    /**
     * Save the original items and field values on the order to
     * the `original_` fields
     */
    public function saveOriginalData()
    {
        // update all items
        DB::table('productorderitem')
            ->where('productorderid', '=', $this->id)
            ->update(['original_variant_id' => DB::raw('productinventoryid')]);

        // update all custom field values
        DB::table('productorderitemfield')
            ->whereIn('orderitemid', $this->items->pluck('id'))
            ->update(['original_value' => DB::raw('value')]);
    }

    /**
     * Setup a payment method and recurring payment profiles from this order.
     *
     * @return array
     */
    public function initializeRecurringPayments()
    {
        // if there are no recurring payments, bail
        if (! $this->hasRecurringItemsInDs()) {
            return [];
        }

        // make sure there is a member
        if (! $this->member) {
            throw new MessageException('Unable to create payment method and recurring payment profile. There is no supporter associated with this contribution.');
        }

        // does the payment method already exist for this member?
        $paymentMethod = $this->paymentMethod ?? PaymentMethod::where('member_id', $this->member_id)->where('token', $this->vault_id)->first();

        // if we didn't find an existing payment method, create payment method using created vault id
        if (! $paymentMethod && $this->vault_id) {
            $paymentMethod = new PaymentMethod;
            $paymentMethod->member_id = $this->member_id;
            $paymentMethod->payment_provider_id = $this->payment_provider_id;
            $paymentMethod->status = 'PENDING';
            $paymentMethod->billing_first_name = $this->billing_first_name;
            $paymentMethod->billing_last_name = $this->billing_last_name;
            $paymentMethod->billing_email = $this->billingemail;
            $paymentMethod->billing_address1 = $this->billingaddress1;
            $paymentMethod->billing_address2 = $this->billingaddress2;
            $paymentMethod->billing_city = $this->billingcity;
            $paymentMethod->billing_state = $this->billingstate;
            $paymentMethod->billing_postal = $this->billingzip;
            $paymentMethod->billing_country = $this->billingcountry;
            $paymentMethod->billing_phone = $this->billingphone;

            if ($paymentMethod->save() === false) {
                throw new MessageException('Unable to create payment method.');
            }

            $res = new \Ds\Domain\Commerce\Responses\TransactionResponse($this->paymentProvider, [
                'completed' => true,
                'response' => 1,
                'response_text' => $this->response_text,
                'transaction_id' => $this->confirmationnumber,
                'cc_number' => $this->billingcardlastfour,
                'cc_exp' => trim("$this->billing_card_expiry_month $this->billing_card_expiry_year"),
                'source_token' => $this->vault_id,
            ]);

            $paymentMethod->updateWithTransactionResponse($res);
        }

        $rpps_created = [];

        // create recurring payment profiles for each of the recurring items
        foreach ($this->items as $item) {
            // if it's not supposed to be processed in GC, bail and skip
            if (sys_get('rpp_donorperfect') || $item->recurring_with_dpo || ! $item->recurring_frequency) {
                continue;
            }

            // setup the recurring payment profile
            $rpp = \Ds\Models\RecurringPaymentProfile::createUsingOrderItemAndCartAndPaymentMethod($item, $this, $paymentMethod);

            // save the payment profile
            if (! $rpp->save()) {
                throw new MessageException('Unable to create recurring payment profile.');
            }

            $rpps_created[] = $rpp;
        }

        return $rpps_created;
    }

    /**
     * Get the Cart/Order from the current active session.
     *
     * @param string|null $uuid
     * @return \Ds\Models\Order|null
     */
    public static function getActiveSession($uuid = null)
    {
        if (empty($uuid)) {
            $uuid = session('cart_uuid');

            if ($uuid) {
                $cart = static::getActiveSession($uuid);

                if ($cart && ! $cart->is_paid) {
                    return $cart;
                }
            }

            return self::getActiveSession(cart_init(true));
        }

        return reqcache("cart:$uuid", function () use ($uuid) {
            return static::where('client_uuid', $uuid)->first();
        });
    }

    /**
     * Loop over every item in the order and determine whether tribtues need to be created.
     *
     * @return void
     */
    public function createTributes()
    {
        \Ds\Models\Tribute::createFromOrder($this);
    }

    /**
     * Apply promocode(s) to an order.
     *
     * @param \Illuminate\Support\Collection|array|string $promo_codes
     * @return array a list of promo codes that were successfully applied
     */
    public function applyPromos($promo_codes)
    {
        // if this order has already been paid for, bail
        if ($this->is_paid) {
            throw new MessageException("Cannot apply promocodes to an contribution that's already been paid for.");
        }

        // if $promo_codes is not a collection make it one
        if (is_array($promo_codes)) {
            $promo_codes = collect($promo_codes);
        }

        if (is_string($promo_codes)) {
            $promo_codes = \Ds\Models\PromoCode::whereIn('code', explode(',', $promo_codes))->get();
        }

        if (! is_a($promo_codes, 'Illuminate\Support\Collection')) {
            return [];
        }

        $items = $this->items()->with('variant.product.categories')->get();

        // track how many times we apply a promo
        $applied_promos = [];

        foreach ($items as $item) {
            if ($promo_codes->whereIn('discount_type', ['bxgy_dollar', 'bxgy_percent'])->count()) {
                break;
            }

            // skip over non-product based items (i.e. sponsorships)
            if (! $item->variant) {
                continue;
            }

            // skip over donation items if NOT in POS
            if (! $this->is_pos && $item->variant->is_donation) {
                continue;
            }

            // discover promo pricing (if available)
            // this will return the best promo pricing based on all provided promos
            $promo_pricing = \Ds\Models\PromoCode::discountByProduct($item->variant->productid, $item->original_price, $promo_codes, $this->currency_code);

            // if there is a valid discount
            if ($promo_pricing) {
                // apply it
                \Ds\Models\OrderItem::where('id', (int) $item->id)->update([
                    'price' => $promo_pricing->price,
                    'promocode' => $promo_pricing->promo,
                ]);

                // increment applied_count
                $applied_promos[] = $promo_pricing->promo;
            }
        }

        // loop over each promo code and see if there is free shipping
        foreach ($promo_codes->where('is_free_shipping', true) as $promo) {
            // set free shipping
            $this->shipping_method_id = null;
            $this->courier_method = ($promo->free_shipping_label) ? $promo->free_shipping_label : 'Free Shipping';
            $this->shipping_amount = 0.00;
            $this->is_free_shipping = true;

            // add this promo to the list of applied promos
            $applied_promos[] = $promo->code;
            break;
        }

        // apply BXGY promocodes to the entire order (minus taxes)
        $promo_codes->whereIn('discount_type', ['bxgy_dollar', 'bxgy_percent'])->each(function ($promo) use ($items, &$applied_promos) {
            if (empty($promo->buy_quantity)) {
                return;
            }

            $bought_quantity = 0;
            $total_amount = $this->totalamount - $this->taxtotal + $this->discount;

            $product_ids = $promo->products()->pluck('productid');
            $category_ids = $promo->categories()->pluck('categoryid');

            foreach ($items as $item) {
                if ($item->locked_to_item_id) {
                    continue;
                }
                if ($product_ids->contains($item->variant->productid)) {
                    $bought_quantity += $item->qty;
                } elseif ($item->variant->product->categories->whereIn('id', $category_ids)->count()) {
                    $bought_quantity += $item->qty;
                }
            }

            $allocation_count = min(floor($bought_quantity / $promo->buy_quantity), $promo->allocation_limit ?? 99999999999999);

            if ($allocation_count) {
                if ($promo->discount_type === 'bxgy_percent') {
                    $this->discount = min($total_amount, ($allocation_count * abs($promo->discount) / 100) * $total_amount);
                } else {
                    $discount = money(abs($promo->discount))
                        ->toCurrency($this->currency)
                        ->getAmount();

                    $this->discount = min($total_amount, $allocation_count * $discount);
                }

                $this->promocode = $promo->code;
                $applied_promos[] = $promo->code;
            } else {
                $this->promocode = null;
                $this->discount = 0;
            }
        });

        // apply order level discounts to each order item
        if ($this->discount && $this->subtotal > 0) {
            $discount = 1 - min(1, $this->discount / $items->sum('original_total'));

            foreach ($items as $item) {
                \Ds\Models\OrderItem::where('id', $item->id)->update([
                    'price' => $item->original_price * $discount,
                    'promocode' => $this->promocode,
                ]);
            }
        }

        // list all applied promos on the order itself
        $this->promoCodes()->sync($applied_promos);

        // reload promocode relationship
        $this->load('promoCodes');

        // recalculate totals
        $this->calculate();

        // return true if we applied more than 0 codes
        return $applied_promos;
    }

    /**
     * Clear all promos.
     *
     * **TO DO**
     * - leverage the new $this->clearPromo() function
     *   to centralize the promo removal code
     *
     * @return void
     */
    public function clearPromos()
    {
        // if this order has already been paid for, bail
        if ($this->is_paid) {
            throw new MessageException("Cannot clear promocodes from an contribution that's already been paid for.");
        }

        // loop over each item
        foreach ($this->items as $item) {
            // if there is a promocode
            if ($item->promocode) {
                // remove the promo (but do it in a way that doesn't trigger
                // the observer and cause recurssion)

                $item->price = $item->original_price;
                $item->promocode = null;

                // updating the database directly without triggering the observer
                \Ds\Models\OrderItem::where('id', (int) $item->id)->update([
                    'price' => $item->price,
                    'promocode' => $item->promocode,
                ]);
            }
        }

        $this->promocode = null;
        $this->discount = 0;

        // detach all promos
        $this->promoCodes()->detach();

        // reset shipping
        $this->shipping_method_id = null;
        $this->courier_method = null;
        $this->shipping_amount = 0.00;
        $this->is_free_shipping = false;

        // recalcaulte
        $this->calculate();
    }

    /**
     * Remove a single promocode.
     *
     * @param string $promo
     * @param bool $skip_recalc Optionally skip recalc
     * @return void
     */
    public function removePromo($promo, $skip_recalc = false)
    {
        // if this order has already been paid for, bail
        if ($this->is_paid) {
            throw new MessageException("Cannot remove a promocode from an contribution that's already been paid for.");
        }

        // loop over each item
        foreach ($this->items as $item) {
            // if there is a promocode
            if ($item->promocode == $promo) {
                // remove the promo (but do it in a way that doesn't trigger
                // the observer and cause recurssion)
                $item->price = $item->original_price;  // manually update the item price to its original price
                $item->promocode = null;               // manually clear the promocode on the item

                // updating the database directly without triggering the observer
                \Ds\Models\OrderItem::where('id', (int) $item->id)->update([
                    'price' => $item->price,
                    'promocode' => $item->promocode,
                ]);
            }
        }

        if ($this->promocode === $promo) {
            $this->promocode = null;
            $this->discount = 0;
        }

        // detach all promos
        $this->promoCodes()->detach($promo);

        // FOR FUTURE USE (we may not always want to
        // recalculate after every promo we remove)
        if (! $skip_recalc) {
            // reset shipping
            $this->shipping_method_id = null;
            $this->courier_method = null;
            $this->shipping_amount = 0.00;
            $this->is_free_shipping = false;

            // recalcaulte
            $this->calculate();
        }
    }

    /**
     * Clear all promos and reapply.
     *
     * @return void
     */
    public function reapplyPromos()
    {
        // if this order has already been paid for, bail
        if ($this->is_paid) {
            throw new MessageException("Cannot re-apply promocodes on an contribution that's already been paid for.");
        }

        // if no promocodes, bail
        if ($this->promoCodes->count() === 0) {
            return;
        }

        // copy the current collectin of promocodes
        $old_promos = clone $this->promoCodes;

        // remove all promos
        $this->clearPromos();

        // apply them all over again
        $this->applyPromos($old_promos);
    }

    /**
     * Revalidate all per-person promos (if they exist)
     *
     * @return array $messages All thrown validation messages
     */
    public function revalidatePerAccountPromos()
    {
        $messages = [];

        // find all the per-person promos
        $per_account_promos = $this->promocodes->where('is_limited_per_account', true);

        // if there are any per-person promos
        if ($per_account_promos->count() > 0) {
            // loop over every per-person promo and:
            // 1) validate it
            // 2) if its not valid, remove it
            foreach ($per_account_promos as $promo) {
                try {
                    PromoCode::validate($promo, $this->is_pos, $this->billingemail, $this->member);
                } catch (Throwable $e) {
                    $this->removePromo($promo->code);
                    $messages[] = $e->getMessage();
                }
            }
        }

        // return any validation messages
        return $messages;
    }

    /**
     * Save information related to the response from the payment processor.
     *
     * @param \Ds\Domain\Commerce\Responses\TransactionResponse $res
     * @return \Ds\Models\Payment|null
     */
    public function updateWithTransactionResponse(TransactionResponse $res): ?Payment
    {
        $provider = $res->getProvider();

        $this->payment_provider_id = $provider->id;
        $this->vault_id = $res->getSourceToken() ?: null;
        $this->response_text = $res->getResponseText() ?: null;
        $this->confirmationnumber = $res->getTransactionId() ?: null;
        $this->billing_name_on_account = trim("$this->billing_first_name $this->billing_last_name");

        if ($res->getCardExpiry()) {
            $this->billing_card_expiry_month = substr($res->getCardExpiry(), 0, 2);
            $this->billing_card_expiry_year = substr($res->getCardExpiry(), 2, 2);
        }

        $this->billingcardtype = $res->getAccountType();
        $this->billingcardlastfour = $res->getAccountLastFour();

        $this->save();

        return app(OrderService::class)->createPaymentFromTransactionResponse($this, $res);
    }

    /**
     * Convert the model instance to an array.
     *
     * @return array
     */
    public function toArray()
    {
        $attributes = parent::toArray();

        foreach ($this->getMutatedAttributes() as $key) {
            // We want to spin through all the mutated attributes for this model and call
            // the mutator for the attribute. We cache off every mutated attributes so
            // we don't have to constantly check on attributes that actually change.
            if (! array_key_exists($key, $attributes)) {
                continue;
            }

            // Ensure any dates are get serialized
            if (is_instanceof($attributes[$key], \DateTimeInterface::class)) {
                $attributes[$key] = $this->serializeDate($attributes[$key]);
            }
        }

        return $attributes;
    }

    /**
     * Use cart functions within the context of the Order.
     *
     * This is handled by swapping out the cart in the session prior to
     * execution of the cart function and then swapping the original cart
     * back into the session afterwards.
     *
     * @param string $fn
     * @param mixed $args,...
     */
    public function function($fn, ...$args)
    {
        $uuid = session('cart_uuid');

        if (empty($uuid)) {
            return call_user_func_array($fn, $args);
        }

        // swap out default cart
        // (required in order to use the cart functions)
        session(['cart_uuid' => $this->client_uuid]);

        try {
            return call_user_func_array($fn, $args);
        } finally {
            // swap back default cart
            session(['cart_uuid' => $uuid]);
        }
    }

    public function createOrUpdateContribution(): ?Contribution
    {
        if (! $this->confirmationdatetime && ! $this->auth_attempts) {
            return null;
        }

        return app(ContributionService::class)->createOrUpdateFromOrder($this);
    }

    /**
     * Update the UTM tracking data.
     */
    public function updateUtmTracking()
    {
        if (request('utm_source') && ! $this->is_paid) {
            $this->tracking_source = request('utm_source', $this->tracking_source);
            $this->tracking_medium = request('utm_medium', $this->tracking_medium);
            $this->tracking_campaign = request('utm_campaign', $this->tracking_campaign);
            $this->tracking_term = request('utm_term', $this->tracking_term);
            $this->tracking_content = request('utm_content', $this->tracking_content);
            $this->save();
        }
    }

    /**
     * Liquid representation of model.
     */
    public function toLiquid()
    {
        return \Ds\Domain\Theming\Liquid\Drop::factory($this, 'Order');
    }

    /**
     * Change the member_id this order is associated with.
     *
     * This function also goes through all the other data
     * that is linked to the order and tagged by that
     * member_id and updates those references as well,
     * including:
     *
     *  - recurring payment profiles
     *  - payment methods
     *  - sponsored children
     *
     * @param int $member_id
     * @return void
     */
    public function linkToMember($member_id)
    {
        // update the member_id on the order
        $this->member_id = $member_id;
        $this->save();

        // apply account to payments
        $this->payments()->update([
            'source_account_id' => $member_id,
        ]);

        // update all related data
        $this->items->each(function ($item) use ($member_id) {
            // recurring payment profiles
            if ($item->recurringPaymentProfile) {
                $item->recurringPaymentProfile->member_id = $member_id;
                $item->recurringPaymentProfile->save();

                // apply account to payments
                $item->recurringPaymentProfile->payments()->update([
                    'source_account_id' => $member_id,
                ]);

                // payment methods
                if ($item->recurringPaymentProfile->paymentMethod) {
                    $item->recurringPaymentProfile->paymentMethod->member_id = $member_id;
                    $item->recurringPaymentProfile->paymentMethod->save();
                }
            }

            // sponsored children
            if ($item->sponsor) {
                $item->sponsor->member_id = $member_id;
                $item->sponsor->save();
            }
        });
    }

    /**
     * Apply membership to the linked member.
     *
     * @return void
     */
    public function applyMemberships()
    {
        // save reference memberships
        $membership_items = $this->items()
            ->whereHas('variant.membership')
            ->with('variant.membership')
            ->get();

        // if there are memberships
        if ($membership_items->count() > 0) {
            // let's be sure a member exists
            // (why a member wouldn't exist at this point is unknown)
            if (! $this->member) {
                try {
                    $this->createMember();
                } catch (Throwable $e) {
                    return;
                }
            }

            // loop over each item that has a membership
            $membership_items->each(function ($item) {
                // safely add the member to the group/membership
                try {
                    $item->applyGroup();
                } catch (Throwable $e) {
                    return;
                }
            });

            // if the authenticated member matches the order then refresh the
            // member in session so the new memberships are cached
            if (member('id') === $this->member_id) {
                member(null, true);
            }
        }
    }

    /**
     * Grant access to downloads for this order.
     *
     * @param mixed $granted_on
     * @return void
     */
    public function grantDownloads($granted_on = null)
    {
        // default value for granted date
        if (! isset($granted_on)) {
            $granted_on = $this->createddatetime;
        }

        // loop over each item in the order
        foreach ($this->items as $item) {
            // if the item has a variant with a file linked to it
            if (isset($item->variant->file)) {
                // if there's already a record granting this download,
                // skip this record
                if (DB::table('productorderitemfiles')
                    ->where('fileid', $item->variant->file->fileid)
                    ->where('orderitemid', $item->id)
                    ->count() > 0) {
                    continue;
                }

                // insert into the granted downloads table
                DB::table('productorderitemfiles')->insert([
                    'granted' => $granted_on,
                    'fileid' => (is_int($item->variant->file->fileid)) ? (int) $item->variant->file->fileid : null,
                    'external_resource_uri' => $item->variant->file->external_resource_uri,
                    'description' => $item->variant->file->description,
                    'orderitemid' => (int) $item->id,
                    'expiration' => (int) ($item->variant->file->expiry_time > -1) ? $this->confirmationdatetime->copy()->addSeconds($item->variant->file->expiry_time)->format('U') : -1,
                    'address_limit' => (int) ($item->variant->file->address_limit > -1) ? $item->variant->file->address_limit : -1,
                    'download_limit' => (int) ($item->variant->file->download_limit > -1) ? $item->variant->file->download_limit : -1,
                    'addresses' => '[]',
                ]);
            }
        }
    }

    /**
     * Get the default system sources.
     *
     * @return array
     */
    public static function getSystemSources()
    {
        $sources = self::$system_sources;

        if (feature('legacy_importer')) {
            $sources[] = 'Legacy Importer';
        }

        sort($sources);

        return $sources;
    }

    /**
     * Send a notification to the member
     */
    public function notify()
    {
        $params = $this->notifyParams();

        dispatch(new SendSupporterContributionAcknowledgmentMail($this));

        // TO DO: All order based notifications should move here
        $this->items->each(function (OrderItem $item) use ($params) {
            // Fundraising Notification Email
            if ($item->fundraisingPage && $item->fundraisingPage->memberOrganizer) {
                $fundraiser_tags = $item->fundraisingPage->getMergeTags($params);
                $item->fundraisingPage->memberOrganizer->notify('fundraising_page_donation_received', $fundraiser_tags, false);
            }

            if ($item->variant) {
                // Custom Variant Emails
                $item->variant->emails->each(function (Email $email) use ($item, $params) {
                    $item->notify($email, $params);
                });

                // Custom Product Emails
                $item->variant->product->emails->each(function ($email) use ($item, $params) {
                    $item->notify($email, $params);
                });
            }
        });
    }

    /**
     * Get all of the order receipt notification merge tags.
     *
     * @return array
     */
    public function notifyParams()
    {
        app(LocaleService::class)->useSiteLocale();

        $params = [];
        $params['bill_title'] = $this->billing_title;
        $params['bill_first_name'] = $this->billing_first_name;
        $params['bill_last_name'] = $this->billing_last_name;
        $params['bill_organization_name'] = $this->billing_organization_name;
        $params['bill_email'] = $this->billingemail;
        $params['bill_address_01'] = $this->billingaddress1;
        $params['bill_address_02'] = $this->billingaddress2;
        $params['bill_city'] = $this->billingcity;
        $params['bill_state'] = $this->billingstate;
        $params['bill_zip'] = $this->billingzip;
        $params['bill_country'] = $this->billingcountry;
        $params['bill_phone'] = $this->billingphone;
        $params['ship_title'] = $this->shipping_title;
        $params['ship_first_name'] = $this->shipping_first_name;
        $params['ship_last_name'] = $this->shipping_last_name;
        $params['ship_organization_name'] = $this->shipping_organization_name;
        $params['ship_email'] = $this->shipemail;
        $params['ship_address_01'] = $this->shipaddress1;
        $params['ship_address_02'] = $this->shipaddress2;
        $params['ship_city'] = $this->shipcity;
        $params['ship_state'] = $this->shipstate;
        $params['ship_zip'] = $this->shipzip;
        $params['ship_country'] = $this->shipcountry;
        $params['ship_phone'] = $this->shipphone;
        $params['order_number'] = $this->invoicenumber;
        $params['total_amount'] = money($this->totalamount, $this->currency_code)->format('$0,0.00 [$$$]');
        $params['bill_card_type'] = $this->payment_type_localized; // $this->billingcardtype;
        $params['bill_card_last_4'] = $this->billingcardlastfour;
        $params['confirmation_number'] = $this->confirmationnumber;
        $params['order_date'] = toLocalFormat($this->createddatetime, trans('emails.order.date'));
        $params['public_tracking_url'] = secure_site_url(route('order_review', $this->invoicenumber, false));
        $params['admin_tracking_url'] = secure_site_url(route('backend.orders.edit', $this->id, false));
        $params['special_notes'] = $this->comments;
        $params['account_referral_link'] = optional($this->member)->getShareableLink('/');

        // pos/payment fields
        $params['payment_type'] = $this->payment_type_localized;
        $params['payment_type_description'] = $this->payment_type_description;
        $params['recurring_description'] = $this->recurring_description;

        // build invoice table
        $invoice_table = '<table cellpadding="2" cellspacing="0" border="1" style="border-collapse:collapse;">';
        $invoice_table .= '<tr>';
        $invoice_table .= '<th style="font-size:12px;">' . trans('emails.invoice.code') . '</th>';
        $invoice_table .= '<th style="font-size:12px;">' . trans('emails.invoice.name') . '</th>';
        $invoice_table .= '<th style="font-size:12px;">' . trans('emails.invoice.qty') . '</th>';
        $invoice_table .= '<th style="font-size:12px;">' . trans('emails.invoice.price') . '</th>';
        $invoice_table .= '<th style="font-size:12px;">' . trans('emails.invoice.total') . '</th>';
        $invoice_table .= '</tr>';
        foreach ($this->items as $item) {
            $custom_fields = [];

            if ($item->variant !== null) {
                $invoice_table .= '<tr>';
                $invoice_table .= '<td style="font-size:12px;">' . $item->code . '</td>';
                $invoice_table .= '<td style="font-size:12px;">';
                $invoice_table .= $item->variant->product->name . ((trim($item->variant->variantname) !== '') ? ' (' . $item->variant->variantname . ')' : '');
                if ($item->designation) {
                    $invoice_table .= '<br><small>' . trans('emails.invoice.designation') . ": <strong>$item->designation</strong></small>";
                }
                if ($item->fields) {
                    $invoice_table .= '<br><small>';
                    foreach ($item->fields as $field) {
                        if ($field->type != 'hidden' && $field->type != 'html') {
                            $invoice_table .= $field->name . ': <strong>' . $field->value_formatted . '</strong><br>';
                        }
                    }
                    $invoice_table .= '</small>';
                }
                $invoice_table .= '</td>';
                $invoice_table .= '<td style="font-size:12px;">' . $item->qty . '</td>';
                if ($item->is_locked) {
                    $invoice_table .= '<td colspan="2"></td>';
                } else {
                    $invoice_table .= '<td style="font-size:12px;">' . number_format($item->locked_variants_price, 2) . '</td>';
                    $invoice_table .= '<td style="font-size:12px;">' . number_format($item->locked_variants_total, 2) . '</td>';
                }
                $invoice_table .= '</tr>';
            }

            if ($item->sponsorship !== null) {
                $invoice_table .= '<tr>';
                $invoice_table .= '<td style="font-size:12px;">' . $item->sponsorship->reference_number . '</td>';
                $invoice_table .= '<td style="font-size:12px;">' . $item->sponsorship->first_name . ' ' . $item->sponsorship->last_name . '</td>';
                $invoice_table .= '<td style="font-size:12px;"></td>';
                $invoice_table .= '<td style="font-size:12px;"></td>';
                $invoice_table .= '<td style="font-size:12px;">' . number_format($item->total, 2) . '</td>';
                $invoice_table .= '</tr>';
            }
        }
        $invoice_table .= '<tr>';
        $invoice_table .= '<td colspan="3" rowspan="5" style="font-size:12px;"></td>';
        $invoice_table .= '<td style="font-size:12px;">' . trans('emails.invoice.subtotal') . '</td>';
        $invoice_table .= '<td style="font-size:12px;">' . number_format($this->subtotal, 2) . '</td>';
        $invoice_table .= '</tr>';
        if ($this->shippable_items || $this->shipping_amount) {
            $invoice_table .= '<tr>';
            $invoice_table .= '<td style="font-size:12px;">' . trans('emails.invoice.shipping') . '</td>';
            $invoice_table .= '<td style="font-size:12px;">' . number_format($this->shipping_amount, 2) . '</td>';
            $invoice_table .= '</tr>';
        }
        $invoice_table .= '<tr>';
        $invoice_table .= '<td style="font-size:12px;">' . trans('emails.invoice.tax') . '</td>';
        $invoice_table .= '<td style="font-size:12px;">' . number_format($this->taxtotal, 2) . '</td>';
        $invoice_table .= '</tr>';
        if ($this->dcc_total_amount) {
            $invoice_table .= '<tr>';
            $invoice_table .= '<td style="font-size:12px;">' . sys_get('dcc_invoice_label') . '</td>';
            $invoice_table .= '<td style="font-size:12px;">' . number_format($this->dcc_total_amount, 2) . '</td>';
            $invoice_table .= '</tr>';
        }
        $invoice_table .= '<tr>';
        $invoice_table .= '<td style="font-size:12px; font-weight:bold;">' . trans('emails.invoice.total') . '</td>';
        $invoice_table .= '<td style="font-size:12px; font-weight:bold;">' . number_format($this->totalamount, 2) . '</td>';
        $invoice_table .= '</tr>';
        $invoice_table .= '</table>';
        $params['invoice_table'] = $invoice_table;

        $legacy_params = [
            'firstname' => $this->billing_first_name,
            'invoicenumber' => $this->invoicenumber,
            'totalamount' => number_format($this->totalamount, 2),
            'billingcardtype' => $this->billingcardtype,
            'billingcardlastfour' => $this->billingcardlastfour,
            'confirmationnumber' => $this->confirmationnumber,
            'trackingurl' => secure_site_url(route('order_review', $this->invoicenumber, false)),
            'shoporganization' => sys_get('clientName'),
            'shopurl' => secure_site_url(),
            'lastname' => $this->billing_last_name,
            'orderdate' => toLocalFormat($this->createddatetime, 'D, M j, Y'),
            'billingname' => $this->billing_first_name . ' ' . $this->billing_last_name,
            'billingemail' => $this->billingemail,
            'billingaddress1' => $this->billingaddress1,
            'billingaddress2' => $this->billingaddress2,
            'billingcity' => $this->billingcity,
            'billingstate' => $this->billingstate,
            'billingzip' => $this->billingzip,
            'billingcountry' => $this->billingcountry,
            'billingphone' => $this->billingphone,
            'shipname' => $this->shipname,
            'shipemail' => $this->shipemail,
            'shipaddress1' => $this->shipaddress1,
            'shipaddress2' => $this->shipaddress2,
            'shipcity' => $this->shipcity,
            'shipstate' => $this->shipstate,
            'shipzip' => $this->shipzip,
            'shipcountry' => $this->shipcountry,
            'shipphone' => $this->shipphone,
            'isrecurring' => '',
            'istribute' => '',
            'pledgeinterval' => '',
            'pledgeday' => '',
        ];

        // Reset locale.
        app(LocaleService::class)->resetLocale();

        return array_merge($params, $legacy_params);
    }

    protected function getUserAgentColumnName(): string
    {
        return 'client_browser';
    }
}
