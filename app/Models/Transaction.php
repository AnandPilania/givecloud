<?php

namespace Ds\Models;

use Carbon\Carbon;
use Ds\Domain\Commerce\Currency;
use Ds\Domain\Commerce\Enums\CredentialOnFileInitiatedBy;
use Ds\Domain\Commerce\Exceptions\GatewayException;
use Ds\Domain\Commerce\Exceptions\PaymentException;
use Ds\Domain\Commerce\SourceTokenChargeOptions;
use Ds\Domain\Shared\Exceptions\MessageException;
use Ds\Eloquent\Hashids;
use Ds\Illuminate\Database\Eloquent\Factories\HasFactory;
use Ds\Illuminate\Database\Eloquent\Model;
use Ds\Models\Observers\TransactionObserver;
use Ds\Models\Traits\HasExternalReferences;
use Ds\Models\Traits\HasLedgerEntries;
use Ds\Services\ContributionService;
use Ds\Services\DonorPerfectService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Support\Str;
use Throwable;

class Transaction extends Model
{
    use HasExternalReferences;
    use HasFactory;
    use Hashids {
        decodeHashid as decodeUnPrefixedHashId;
    }
    use HasLedgerEntries;

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
        'transaction_status' => 'New',
        'transaction_type' => 'Cart',
        'currency_code' => 'USD',
        'tax_amt' => 0.00,
        'shipping_amt' => 0.00,
        'payment_status' => 'None',
        'transaction_log' => '',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'order_time',
        'refunded_at',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'amt' => 'double',
        'dcc_amount' => 'double',
        'tax_amt' => 'double',
        'shipping_amt' => 'double',
        'functional_exchange_rate' => 'double',
        'functional_total' => 'double',
        'refunded_amt' => 'double',
        'dp_auto_sync' => 'boolean',
    ];

    protected $with = ['recurringPaymentProfile'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'is_payment_accepted',
        'payment_description',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // set the default value of dp_auto_sync
        // based on the site's default
        if (! isset($this->dp_auto_sync)) {
            $this->dp_auto_sync = sys_get('dp_auto_sync_txns');
        }
    }

    protected static function boot(): void
    {
        parent::boot();

        self::observe(new TransactionObserver);
    }

    public function contribution(): BelongsTo
    {
        return $this->belongsTo(Contribution::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class)
            ->withTrashed();
    }

    public function taxReceipts(): BelongsToMany
    {
        return $this->belongsToMany(TaxReceipt::class, 'tax_receipt_line_items', 'transaction_id', 'tax_receipt_id')
            ->using(TaxReceiptLineItem::class);
    }

    public function recurringBatch(): BelongsTo
    {
        return $this->belongsTo(RecurringBatch::class);
    }

    public function recurringPaymentProfile(): BelongsTo
    {
        return $this->belongsTo(RecurringPaymentProfile::class);
    }

    public function refundedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'refunded_by');
    }

    public function dunningTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'parent_transaction_id');
    }

    public function payments(): BelongsToMany
    {
        return $this->belongsToMany(Payment::class, 'payments_pivot', 'transaction_id', 'payment_id');
    }

    public function latestPayment(): HasOneThrough
    {
        return $this->hasOneThrough(Payment::class, PaymentPivot::class, 'transaction_id', 'id', null, 'payment_id')->latest();
    }

    public function successfulPayments(): BelongsToMany
    {
        return $this->payments()
            ->succeededOrPending();
    }

    /**
     * Scope: failed payments
     *
     * @return bool
     */
    public function scopeFailed($query)
    {
        return $query->where('payment_status', '!=', 'Completed');
    }

    /**
     * Scope: failed payments
     *
     * @return bool
     */
    public function scopeSucceeded($query)
    {
        return $query->where('payment_status', '=', 'Completed');
    }

    /**
     * Scope: unsynced payments
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeUnsynced($query)
    {
        if (dpo_is_enabled()) {
            return $query->succeeded()
                ->where('dp_auto_sync', true)
                ->whereNull('dpo_gift_id');
        }

        return $query->whereRaw('0 = 1');
    }

    /**
     * Scope: synced payments
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeSynced($query)
    {
        return $query->succeeded()->whereNotNull('dpo_gift_id');
    }

    /**
     * Scope: Refunded orders
     *
     * @param \Illuminate\Database\Query\Builder $query
     */
    public function scopeRefunded($query)
    {
        return $query->whereNotNull('refunded_amt');
    }

    /**
     * Scope: NOT Refunded orders
     *
     * @param \Illuminate\Database\Query\Builder $query
     */
    public function scopeNotRefunded($query)
    {
        return $query->whereNull('refunded_amt');
    }

    /**
     * Scope: Transactions from this month.
     *
     * @param \Illuminate\Database\Query\Builder $query
     */
    public function scopeThisMonth($query)
    {
        return $query->whereBetween('order_time', [
            toUtc(fromLocal('now')->startOfMonth()),
            toUtc(fromLocal('now')->endOfMonth()),
        ]);
    }

    public function scopeOrderedBefore(Builder $query, string $date): Builder
    {
        return $query->where('order_time', '<', Carbon::parse($date));
    }

    public function scopeOrderedAfter(Builder $query, string $date): Builder
    {
        return $query->where('order_time', '>', Carbon::parse($date));
    }

    public function getPrefixedIdAttribute(): ?string
    {
        return 'txn_' . $this->hashid;
    }

    public static function decodeHashid($hashid)
    {
        return static::decodeUnPrefixedHashId(Str::remove('txn_', $hashid));
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
     * Attribute Mask: is_payment_accepted (Payment was accepted by Merchant)
     *
     * @return bool
     */
    public function getIsPaymentAcceptedAttribute()
    {
        return $this->payment_status === 'Completed';
    }

    /**
     * Attribute Mask: is_payment_accepted (Payment was accepted by Merchant)
     *
     * @return bool
     */
    public function getIsUnsyncedAttribute()
    {
        if (! dpo_is_enabled()) {
            return false;
        }

        return $this->dp_auto_sync
            && $this->is_payment_accepted
            && ! $this->dpo_gift_id;
    }

    /**
     * Grab last transaction log entry
     *
     * @return string
     */
    public function getLastTransactionLogAttribute()
    {
        return array_values(array_slice(explode("\n", trim($this->transaction_log)), -1))[0];
    }

    public function getShippingAmountAttribute(): float
    {
        return $this->shipping_amt;
    }

    public function getTaxTotalAttribute(): float
    {
        return $this->tax_amt;
    }

    public function getDccTotalAmountAttribute(): float
    {
        return $this->dcc_amount;
    }

    public function getSubtotalAmountAttribute(): float
    {
        return round($this->amt - $this->tax_amt - $this->dcc_amount - $this->shipping_amt, 2);
    }

    public function getBalanceAmountAttribute(): float
    {
        return $this->amt - $this->refunded_amt;
    }

    /**
     * Attribute Mask: is_refunded
     *
     * @return bool
     */
    public function getIsRefundedAttribute($value)
    {
        return isset($this->refunded_amt) && $this->refunded_amt !== 0;
    }

    /**
     * Attribute Mask: is_refundable
     *
     * @return bool
     */
    public function getIsRefundableAttribute($value)
    {
        return $this->is_payment_accepted && $this->transaction_id && ! $this->is_refunded;
    }

    /**
     * Attribute Mask: payment_description
     *
     * @return string|null
     */
    public function getPaymentDescriptionAttribute()
    {
        if (in_array($this->payment_method_type, ['eft', 'cash', 'check', 'other'])) {
            return ($this->payment_method_type == 'eft') ? 'EFT' : ucwords($this->payment_method_type);
        }

        if ($this->paymentMethod) {
            return ucwords($this->paymentMethod->account_type);
        }
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

        if (empty($this->functional_total)) {
            $this->functional_total = $this->amt * $this->functional_exchange_rate;
        }
    }

    /**
     * Attribute Mutator: Amt
     *
     * @param mixed $value
     */
    public function setAmtAttribute($value)
    {
        $this->attributes['amt'] = $value;

        $this->functional_total = $value * $this->functional_exchange_rate;
    }

    /**
     * Processes the transaction.
     *
     * @return \Ds\Domain\Commerce\Responses\TransactionResponse|null
     */
    public function process()
    {
        if ($this->transaction_status !== 'New') {
            throw new MessageException('Transactions are immutable after being processed.');
        }

        $res = null;
        $this->transaction_status = 'Active';
        $this->transactionLog('processing transaction');

        // process a offline payment type
        if (in_array($this->payment_method_type, ['eft', 'cash', 'check', 'other'])) {
            $this->transactionLog("logging manual charge: {$this->payment_method_type}");
            $this->payment_status = 'Completed';

        // process a gateway payment
        } else {
            // Attempt to charge the Payment Method
            $res = $this->chargePaymentMethod();

            if ($res === false) {
                return null;
            }
        }

        // Attempt to generate tax receipt
        // The success/failure of this is inconsequential (we don't care if it fails or succeeds)
        if (sys_get('tax_receipt_pdfs') && $this->tax_receipt_type === 'single') {
            $this->transactionLog('issuing a tax receipt');

            // create and return a new tax receipt
            try {
                $tax_receipt = $this->issueTaxReceipt();
                $this->transactionLog("tax receipt {$tax_receipt->number} issued & notified");
            } catch (Throwable $e) {
                $this->transactionLog($e);
            }
        }

        // Update the fundraising totals
        if ($this->is_payment_accepted && isset($this->recurringPaymentProfile->order_item->fundraisingPage)) {
            $this->recurringPaymentProfile->order_item->fundraisingPage->updateAggregates();
        }

        $this->transaction_status = 'Completed';
        $this->transactionLog('transaction completed');

        return $res;
    }

    /**
     * Charges the payment method.
     *
     * @return \Ds\Domain\Commerce\Responses\TransactionResponse|bool|null
     */
    protected function chargePaymentMethod()
    {
        // skip if payment is already completed then skip charging
        if ($this->payment_status !== 'None') {
            $this->transactionLog('already charged via Payment');

            return null;
        }

        if (! $this->paymentMethod || $this->paymentMethod->deleted_at) {
            $this->transaction_status = 'Error';
            $this->transactionLog('profile is missing a payment method');

            return false;
        }

        $this->transactionLog("sending charge using method: {$this->paymentMethod->display_name}");

        try {
            try {
                $res = $this->paymentMethod->charge(
                    $this->amt,
                    $this->currency_code,
                    new SourceTokenChargeOptions([
                        'dccAmount' => $this->dcc_amount,
                        'initiatedBy' => CredentialOnFileInitiatedBy::MERCHANT,
                        'recurring' => true,
                    ]),
                );

                app('activitron')->increment('Site.payments.success');
            } catch (PaymentException $e) {
                app('activitron')->increment('Site.payments.failure');

                $res = $e->getResponse();
            }

            $this->transactionLog($res);

            switch ((int) $res->getResponse()) {
                case 1: $this->payment_status = 'Completed'; break;
                case 2: $this->payment_status = 'Denied'; break;
                case 3: $this->payment_status = 'Failed'; break;
            }

            $this->reason_code = $res->getResponseText();
            $this->transaction_id = $res->getTransactionId();
            $this->transactionLog('finished sending charge using payment method');
        } catch (Throwable $e) {
            app('activitron')->increment('Site.payments.failure');

            $this->transaction_status = 'Error';
            $this->transactionLog('transaction failed due to an exception: ' . $e->getMessage());

            return false;
        }

        return $res;
    }

    /**
     * Commit the transaction to DPO.
     *
     * @return bool
     */
    public function commitToDPO()
    {
        // don't push to dpo if no payment was accepted
        if (! $this->is_payment_accepted) {
            $this->transactionLog('cannot send data to DPO - no payment');

            return false;
        }

        try {
            $this->transactionLog('pushing to dp');
            $pushed_data = app(DonorPerfectService::class)->pushTransaction($this);
        } catch (\Exception $e) {
            notifyException($e);
            $this->transactionLog('failed to push to dp. ' . $e->getMessage());

            return false;
        }

        $this->transactionLog('successfully pushed to dp: ' . json_encode($pushed_data, JSON_PRETTY_PRINT));

        if ($this->is_refunded) {
            $this->commitRefundToDPO();
        }

        return true;
    }

    /**
     * Commit the refunded transaction to DPO.
     *
     * @return bool
     */
    public function commitRefundToDPO()
    {
        // don't push to dpo if no refund was made
        if (! $this->refunded_amt) {
            $this->transactionLog('cannot send refund data to DPO - no refund');

            return false;
        }

        $this->transactionLog('pushing to refund dp');

        try {
            $pushed_data = app(DonorPerfectService::class)->pushTransactionFullRefund($this);
        } catch (\Exception $e) {
            notifyException($e);
            $this->transactionLog('failed to push refund to dp. ' . $e->getMessage());

            return false;
        }

        $this->transactionLog('successfully pushed refund to dp: ' . json_encode($pushed_data, JSON_PRETTY_PRINT));

        return true;
    }

    /**
     * Attribute Mask: receiptable_amount
     * Loops over all items in the order and sums the amounts paid on items where the product.is_tax_receiptable = true
     *
     * !! TO DO !!
     * !! TO DO !!
     * !! TO DO !!
     * !! TO DO !!
     * !! TO DO !!
     *
     * This attribute is not in use yet.
     * - TaxReceipt::createFromTransaction() should reference $txn->receiptable_amt instead of amt
     * - This function should check the related product to be sure its receiptable
     *
     * @return float
     */
    public function getReceiptableAmtAttribute($value)
    {
        // if this order has been refunded, its ZERO
        if ($this->is_refunded) {
            return 0.00;
        }

        // return the amount of this transaction
        return (float) $this->amt;
    }

    /**
     * Generate tax receipt.
     *
     * @return \Ds\Models\TaxReceipt
     */
    public function issueTaxReceipt($notify_donor = true)
    {
        // generate receipt
        $tax_receipt = \Ds\Models\TaxReceipt::createFromTransaction($this->id);

        // email notify tax receipt
        if ($tax_receipt && $notify_donor) {
            $tax_receipt->notify();
        }

        return $tax_receipt;
    }

    public function createOrUpdateContribution(): ?Contribution
    {
        if (empty($this->latestPayment)) {
            return null;
        }

        return app(ContributionService::class)->createOrUpdateFromPayment($this->latestPayment);
    }

    /**
     * Appends a message to the transaction log.
     *
     * @param \Throwable|string|array $message
     * @return void
     */
    public function transactionLog($message)
    {
        if (is_array($message)) {
            $message = json_encode($message, JSON_PRETTY_PRINT);
        }

        if (is_object($message)) {
            if ($message instanceof Throwable) {
                $message = $message->getMessage();
            } else {
                $message = json_encode($message, JSON_PRETTY_PRINT);
            }
        }

        if (is_bool($message)) {
            $message = $message ? 'TRUE' : 'FALSE';
        }

        $this->transaction_log .= toUtcFormat('now', 'Y-m-d H:i:s') . " -- $message\n";
        $this->save();
    }

    /**
     * Refund the entire order.
     *
     * @return \Ds\Models\Transaction
     */
    public function refund()
    {
        // was the order already refunded?
        if ($this->is_refunded) {
            throw new MessageException('Transaction #' . $this->transaction_id . ' was already refunded on ' . toLocalFormat($this->refunded_at, 'M j, Y') . ' (Auth: ' . $this->refunded_auth . ').');
        }

        // is there an amount to refund?
        if ($this->amt == 0) {
            throw new MessageException('Refund failed for Transaction #' . $this->transaction_id . '. There is no refundable amount.');
        }

        // manual refund
        if (in_array($this->payment_method_type, ['eft', 'cash', 'check', 'other'])) {
            $this->refunded_auth = 'manual';

        // gateway refund
        } else {
            $payment = $this->successfulPayments->first();

            if (empty($payment)) {
                throw new MessageException("Refund failed for Transaction #{$this->transaction_id} (recurring profile {$this->recurringPaymentProfile->profile_id}).");
            }

            if (empty($this->paymentMethod)) {
                throw new MessageException("Refund failed for Transaction #{$this->transaction_id}. Unable to refund transactions not linked to a Payment Method (e.x. Legacy Importer).");
            }

            try {
                $res = $this->paymentMethod->paymentProvider->refundCharge(
                    $this->transaction_id,
                    $this->amt,
                    $this->amt === $payment->amount,
                    $this->paymentMethod
                );
            } catch (GatewayException $e) {
                if (count($this->successfulPayments)) {
                    $res = $e->getResponse();

                    $refund = new Refund;
                    $refund->status = 'failed';
                    $refund->amount = $this->amt;
                    $refund->currency = $this->currency_code;
                    $refund->reason = 'requested_by_customer';
                    $refund->refunded_by_id = user('id');

                    if ($res) {
                        $refund->reference_number = $res->getTransactionId();
                        $refund->refund_audit_log = 'json:' . json_encode($res);
                    } else {
                        $refund->refund_audit_log = $e->getMessage();
                    }

                    $this->successfulPayments->first()->refunds()->save($refund);
                }

                throw new MessageException('Refund failed for Contribution #' . $this->transaction_id . ' (recurring profile ' . $this->recurringPaymentProfile->profile_id . '). ' . $e->getMessage());
            }
        }

        // update the order
        $this->refunded_auth = isset($res) ? $res->getTransactionId() : $this->refunded_auth;
        $this->refunded_at = now();
        $this->refunded_amt = $this->amt;
        $this->refunded_by = user('id');
        $this->save();

        if (count($this->successfulPayments)) {
            $refund = new Refund;
            $refund->status = isset($res) ? $res->getResponse() : 'succeeded';
            $refund->reference_number = $this->refunded_auth;
            $refund->amount = $this->refunded_amt;
            $refund->currency = $this->currency_code;
            $refund->reason = 'requested_by_customer';
            $refund->refunded_by_id = $this->refunded_by ?? 1;
            $refund->created_at = $this->refunded_at;
            $refund->updated_at = $this->refunded_at;

            $this->successfulPayments->first()->refunds()->save($refund);
        }

        // void/delete tax receipt, if a tax receipt exists
        if ($this->taxReceipt) {
            $this->taxReceipt->void();
        }

        // update profile aggregate amount
        $this->recurringPaymentProfile->refreshAggregateAmount();

        // update dp
        if (sys_get('dp_push_txn_refunds')) {
            $this->commitRefundToDPO();
        }

        // return the order
        return $this;
    }

    /**
     * Dynamically set attributes on the model.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function __set($key, $value)
    {
        $exceptions = [
            'transaction_log',
            'recurring_batch_id',
            'dp_auto_sync',
            'dpo_gift_id',
            'alt_transaction_id',
            'dpo_refund_gift_id',
            'transaction_status',
            'refunded_at',
            'refunded_amt',
            'refunded_auth',
            'refunded_by',
        ];

        // Prevent modifications to Transactions once
        // they have been processed essentially making them immutable
        if (in_array($this->transaction_status, ['Completed', 'Error'])
                && ! $this->isDirty('transaction_status')
                && ! in_array($key, $exceptions)) {
            throw new MessageException('Transactions are immutable after being processed.');
        }

        parent::__set($key, $value);
    }
}
