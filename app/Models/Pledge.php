<?php

namespace Ds\Models;

use Ds\Domain\Commerce\Currency;
use Ds\Domain\Theming\Liquid\Liquidable;
use Ds\Eloquent\Hashids;
use Ds\Eloquent\SoftDeleteUserstamp;
use Ds\Eloquent\Userstamps;
use Ds\Illuminate\Database\Eloquent\Factories\HasFactory;
use Ds\Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Pledge extends Model implements Liquidable
{
    use Hashids;
    use HasFactory;
    use SoftDeletes;
    use SoftDeleteUserstamp;
    use Userstamps;

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'end_date' => 'date',
        'first_donation_date' => 'date',
        'funded_amount' => 'float',
        'funded_percent' => 'float',
        'is_active' => 'boolean',
        'last_donation_date' => 'date',
        'start_date' => 'date',
        'total_amount' => 'float',
        'functional_exchange_rate' => 'double',
        'functional_total_amount' => 'double',
        'functional_funded_amount' => 'double',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(PledgeCampaign::class, 'pledge_campaign_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Scope: Active
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeActive($query)
    {
        $query->where(function ($q) {
            $q->whereNull('end_date')->orWhere('end_date', '>=', fromLocal('today'));
        });
    }

    /**
     * Scope: Expired
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeExpired($query)
    {
        $query->where(function ($q) {
            $q->whereNotNull('end_date')
                ->where('end_date', '<', fromLocal('today'));
        });
    }

    /**
     * Scope: Funded
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeFunded($query)
    {
        $query->whereIn('funding_status', ['funded', 'overfunded']);
    }

    /**
     * Scope: Underfunded
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeUnderfunded($query)
    {
        $query->where('funding_status', '=', 'underfunded');
    }

    /**
     * Query: Order Items
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function orderItems()
    {
        return $this->campaign->orderItems()
            ->where('productorder.member_id', '=', $this->account_id);
    }

    /**
     * Query: Transactions
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function transactions()
    {
        return $this->campaign->transactions()
            ->where('recurring_payment_profiles.member_id', '=', $this->account_id);
    }

    /**
     * Attribute Mutator: Total Amount
     *
     * @param mixed $value
     * @return void
     */
    public function setTotalAmountAttribute($value): void
    {
        $this->attributes['total_amount'] = $value;

        $this->functional_total_amount = $value * $this->functional_exchange_rate;
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

        if (empty($this->functional_total_amount)) {
            $this->functional_total_amount = $this->total_amount * $this->functional_exchange_rate;
        }

        if (empty($this->functional_funded_amount)) {
            $this->functional_funded_amount = $this->funded_amount * $this->functional_exchange_rate;
        }
    }

    /**
     * Get the merge tags for this pledge
     * when sending emails.
     *
     * @param array $additionalTags
     * @return array
     */
    public function getMergeTags(array $additionalTags = []): array
    {
        $tags = array_merge(global_merge_tags(), [
            'pledge_name' => $this->campaign->name,
            'pledge_number' => $this->hashid,
            'pledge_amount' => (string) money($this->total_amount, $this->currency_code),
            'pledge_currency' => $this->currency_code,
        ]);

        return $tags + $additionalTags;
    }

    /**
     * Collection: all the related payments on this pledge
     *
     * @return \Illuminate\Database\Query\Builder|\Illuminate\Support\Collection $query
     */
    public function getPayments()
    {
        $payments = collect([]);

        if (! $this->campaign) {
            return $payments;
        }

        foreach ($this->transactions()->select('transactions.*')->with('recurringPaymentProfile')->get() as $txn) {
            $payments->push((object) [
                'order_date' => $txn->order_time,
                'reference' => 'Recurring Profile ' . $txn->recurringPaymentProfile->profile_id,
                'reference_type' => 'Recurring Profile',
                'reference_id' => $txn->recurringPaymentProfile->profile_id,
                'description' => $txn->recurringPaymentProfile->description,
                'amount' => $txn->amt,
            ]);
        }

        foreach ($this->orderItems()->select('productorderitem.*')->with('order')->get() as $item) {
            $payments->push((object) [
                'order_date' => $item->order->confirmationdatetime,
                'reference' => 'Contribution #' . $item->order->invoicenumber,
                'reference_type' => 'Order',
                'reference_id' => $item->order->id,
                'description' => $item->description,
                'amount' => $item->total,
            ]);
        }

        return $payments->sortByDesc('order_date');
    }

    /**
     * Calculate
     *
     * @return void
     */
    public function calculate($force_calculate = false)
    {
        // determine whether or not to calculate
        if (! $force_calculate) {
            if ($this->campaign->start_date && $this->campaign->start_date->isFuture()) {
                return;
            }

            if ($this->campaign->end_date && $this->campaign->end_date->isPast()) {
                return;
            }
        }

        $aggregates = DB::query()
            ->fromSub(
                $this->orderItems()
                    ->select([
                        DB::raw("concat('order_', productorder.id) as donation_id"),
                        DB::raw('greatest(0, productorderitem.price * productorderitem.qty - ifnull(productorder.refunded_amt, 0)) as donation_amount'),
                        'productorder.currency_code as donation_currency',
                        'productorder.confirmationdatetime as donation_date',
                        'productorder.functional_exchange_rate as donation_exchange_rate',
                    ])->unionAll(
                        $this->transactions()->select([
                            DB::raw("concat('transaction_', transactions.id) as donation_id"),
                            DB::raw('greatest(0, transactions.amt - ifnull(transactions.refunded_amt, 0)) as donation_amount'),
                            'transactions.currency_code as donation_currency',
                            'transactions.order_time as donation_date',
                            'transactions.functional_exchange_rate as donation_exchange_rate',
                        ])
                    ),
                'agg'
            )->select([
                DB::raw('sum(donation_amount * donation_exchange_rate) as funded_functional_amount'),
                DB::raw('count(donation_id) as funded_count'),
                DB::raw('min(donation_date) as first_donation_date'),
                DB::raw('max(donation_date) as last_donation_date'),
            ])->first();

        $this->functional_funded_amount = $aggregates->funded_functional_amount ?? 0;
        $this->funded_count = $aggregates->funded_count ?? 0;
        $this->funded_amount = money($this->functional_funded_amount)->toCurrency($this->currency_code)->getAmount();
        $this->funded_percent = $this->total_amount ? $this->funded_amount / $this->total_amount : 1.0;
        $this->first_donation_date = $aggregates->first_donation_date ?? null;
        $this->last_donation_date = $aggregates->last_donation_date ?? null;

        if (empty($this->funded_percent)) {
            $this->funded_status = 'unfunded';
        } elseif ($this->funded_percent > 1.0) {
            $this->funded_status = 'overfunded';
        } elseif ($this->funded_percent < 1.0) {
            $this->funded_status = 'underfunded';
        } else {
            $this->funded_status = 'funded';
        }

        // save (but don't trigger observer)
        self::where('id', $this->id)
            ->update([
                'funded_amount' => $this->funded_amount,
                'funded_count' => $this->funded_count,
                'funded_percent' => $this->funded_percent,
                'funded_status' => $this->funded_status,
                'functional_funded_amount' => $this->functional_funded_amount,
                'first_donation_date' => $this->first_donation_date,
                'last_donation_date' => $this->last_donation_date,
            ]);

        // update the campaign aggregates
        $this->campaign->calculate($force_calculate);
    }

    /**
     * Calculate all
     *
     * @return void
     */
    public static function calculateAll()
    {
        self::chunk(200, function ($pledges) {
            $pledges->each(function ($pledge) {
                $pledge->calculate();
            });
        });
    }

    /**
     * Calculate by product and member
     *
     * @return void
     */
    public static function calculateByMemberAndProduct(Member $member, Product $product)
    {
        $member->pledges()
            ->whereHas('campaign.products', function ($q) use ($product) {
                $q->where('id', $product->id);
            })->get()
            ->each(function ($pledge) {
                $pledge->calculate();
            });
    }

    /**
     * Calculate by account
     *
     * @return void
     */
    public static function calculateByAccount($account)
    {
        $account->pledges->each(function ($pledge) {
            $pledge->calculate();
        });
    }

    /**
     * Liquid representation of model.
     */
    public function toLiquid()
    {
        return \Ds\Domain\Theming\Liquid\Drop::factory($this, 'Pledge');
    }
}
