<?php

namespace Ds\Models;

use Carbon\Carbon;
use Ds\Domain\Commerce\Currency;
use Ds\Domain\Theming\Liquid\Liquidable;
use Ds\Eloquent\Hashids;
use Ds\Eloquent\Permissions;
use Ds\Eloquent\SoftDeleteUserstamp;
use Ds\Eloquent\Userstamps;
use Ds\Enums\FundraisingPageType;
use Ds\Illuminate\Database\Eloquent\Auditable;
use Ds\Illuminate\Database\Eloquent\Factories\HasFactory;
use Ds\Illuminate\Database\Eloquent\HasAuditing;
use Ds\Illuminate\Database\Eloquent\Model;
use Ds\Models\Observers\FundraisingPageObserver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Stevebauman\Purify\Facades\Purify;

class FundraisingPage extends Model implements Auditable, Liquidable
{
    use HasAuditing;
    use Hashids;
    use HasFactory;
    use Permissions;
    use SoftDeletes;
    use SoftDeleteUserstamp;
    use Userstamps;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * Attributes to exclude from the Audit.
     *
     * @var array
     */
    protected $auditExclude = [
        'created_by',
        'updated_by',
        'deleted_by',
    ];

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
        'activated_date' => 'date',
        'amount_raised' => 'float',
        'amount_raised_offset' => 'float',
        'donation_count' => 'integer',
        'donation_count_offset' => 'integer',
        'goal_amount' => 'float',
        'goal_deadline' => 'date',
        'report_count' => 'integer',
    ];

    /**
     * Default attributes and values.
     *
     * @var array
     */
    protected $attributes = [
        'type' => FundraisingPageType::WEBSITE,
        'status' => 'draft',  // ['draft','active','deactivated','suspended']
        'privacy' => 'private', // ['private','unlisted','public']
    ];

    /**
     * Attributes hidden from serialization.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'absolute_url',
        'absolute_edit_url',
        'remaining_to_raise',
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        self::observe(new FundraisingPageObserver);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function paidOrderItems(): HasMany
    {
        return $this->orderItems()
            ->whereHas('order', function ($q) {
                $q->paid();
                $q->whereNull('deleted_at');
            });
    }

    public function reports(): HasMany
    {
        return $this->hasMany(FundraisingPageReport::class);
    }

    public function memberOrganizer(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'member_organizer_id', 'id');
    }

    public function photo(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'photo_id');
    }

    public function teamPhoto(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'team_photo_id');
    }

    public function teamFundraisingPage(): BelongsTo
    {
        return $this->belongsTo(FundraisingPage::class, 'team_fundraising_page_id');
    }

    public function teamMembers(): HasMany
    {
        return $this->hasMany(FundraisingPage::class, 'team_fundraising_page_id');
    }

    /**
     * Scope: active
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeActive($query)
    {
        $query->where(function ($query) {
            $query->where('status', '=', 'active');
            $query->where(function ($query) {
                $query->whereNull('goal_deadline');
                $query->orWhere('goal_deadline', '>=', fromLocal('today'));
            });
        });
    }

    /**
     * Scope: closed
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeClosed($query)
    {
        $query->where(function ($query) {
            $query->where('status', '=', 'closed');
            $query->orWhere(function ($query) {
                $query->where('status', '=', 'active');
                $query->where(function ($query) {
                    $query->whereNotNull('goal_deadline');
                    $query->where('goal_deadline', '<', fromLocal('today'));
                });
            });
        });
    }

    public function scopeActiveAndVerified(Builder $query): void
    {
        if (! sys_get('bool:fundraising_pages_requires_verify')) {
            $query->active();

            return;
        }

        $query->active()->whereHas('memberOrganizer', function (Builder $query) {
            $query->verified();
        });
    }

    /**
     * Scope: activeOrClosed
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeActiveOrClosed($query)
    {
        $query->where(function ($query) {
            $query->where('status', '=', 'closed');
            $query->orWhere(function ($query) {
                $query->active();
            });
        });
    }

    public function scopeActiveOrPending(Builder $query): void
    {
        $query->where(function (Builder $query) {
            $query->active()
                ->orWhere(function (Builder $query) {
                    $query->pending();
                });
        });
    }

    public function scopeActiveOrPendingForLoggedInSupporter(Builder $query): void
    {
        if (! member()) {
            $query->active();

            return;
        }

        $query->where(function ($query) {
            $query->active();
            $query->orWhere(function ($query) {
                $query->where('member_organizer_id', member('id'));
            });
        });
    }

    public function scopePending($query)
    {
        $query->where('status', '=', 'pending');
    }

    public function scopeStandaloneType(Builder $query)
    {
        $query->where('type', FundraisingPageType::STANDALONE);
    }

    public function scopeWebsiteType(Builder $query)
    {
        $query->where('type', FundraisingPageType::WEBSITE);
    }

    /**
     * Mutator: absolute_url
     *
     * @return string
     */
    public function getAbsoluteUrlAttribute()
    {
        if ($this->type === FundraisingPageType::STANDALONE) {
            return route('peer-to-peer-campaign.donate', $this->hashid);
        }

        return secure_site_url('/fundraisers/' . $this->url);
    }

    /**
     * Mutator: share_url
     * The URL to the fundraising page for sharing links
     *
     * @return string
     */
    public function getShareUrlAttribute()
    {
        $member = member();

        return $member ? $member->getShareableLink($this->absolute_url) : $this->absolute_url;
    }

    /**
     * Mutator: absolute_edit_url
     *
     * @return string
     */
    public function getAbsoluteEditUrlAttribute()
    {
        return "{$this->absolute_url}/edit";
    }

    /**
     * Mutator: days_left
     *
     * @return int
     */
    public function getDaysLeftAttribute()
    {
        return ($this->goal_deadline && toLocal($this->goal_deadline)->isFuture()) ? toLocal($this->goal_deadline)->diffInDays() : 0;
    }

    /**
     * Mutator: is_over
     *
     * @return bool
     */
    public function getHasEndedAttribute(): bool
    {
        return $this->goal_deadline ? toLocal($this->goal_deadline)->isPast() : false;
    }

    /**
     * Mutator: remaining_to_raise
     *
     * @return int
     */
    public function getRemainingToRaiseAttribute()
    {
        return max(0, (float) $this->goal_amount - (float) $this->amount_raised);
    }

    /**
     * Mutator: total_days
     *
     * @return int
     */
    public function getTotalDaysAttribute()
    {
        return ($this->activated_date && $this->goal_deadline) ? $this->activated_date->diffInDays($this->goal_deadline) : 0;
    }

    /**
     * Mutator: days_left
     *
     * @return int
     */
    public function getDaysElapsedAttribute()
    {
        if ($this->activated_date && $this->goal_deadline) {
            return ($this->total_days > 0) ? $this->total_days - $this->days_left : 0;
        }

        return 0;
    }

    /**
     * Mutator: days_progress_percent
     *
     * Percent of time that has past.
     *
     * @return int
     */
    public function getDaysElapsedPercentAttribute()
    {
        if ($this->activated_date && $this->goal_deadline) {
            return ($this->total_days > 0) ? ($this->days_elapsed / $this->total_days) : 1;
        }

        return 0;
    }

    /**
     * Mutator: status
     *
     * @param string $val
     * @return string
     */
    public function getStatusAttribute($val)
    {
        if ($val == 'active' && $this->goal_deadline && $this->goal_deadline->isPast()) {
            return 'closed';
        }

        return $val;
    }

    /**
     * Attribute Accessor: Video
     *
     * @return \stdClass|null
     */
    public function getVideoAttribute()
    {
        if ($this->video_url) {
            return (object) oembed_get($this->video_url);
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
    }

    /**
     * Attribute Mutator: Goal Amount
     *
     * @param mixed $value
     */
    public function setGoalAmountAttribute($value)
    {
        $this->attributes['goal_amount'] = $value;

        $rate = Currency::getExchangeRate($this->currency_code, $this->functional_currency_code);
        $this->functional_goal_amount = $value * $rate;

        $this->progress_percent = $this->goal_amount > 0 ? $this->amount_raised / $this->goal_amount : 0;
    }

    /**
     * Attribute Mutator: Amount Raised
     *
     * @param mixed $value
     */
    public function setAmountRaisedAttribute($value)
    {
        $this->attributes['amount_raised'] = $value;

        $rate = Currency::getExchangeRate($this->currency_code, $this->functional_currency_code);
        $this->functional_amount_raised = $value * $rate;

        $this->progress_percent = ($this->goal_amount > 0) ? ($this->amount_raised / $this->goal_amount) : 0;
    }

    /**
     * Attribute Mutator: Title
     *
     * @param string $value
     */
    public function setTitleAttribute($value)
    {
        $this->attributes['title'] = strip_tags($value);
    }

    /**
     * Attribute Mutator: Description
     *
     * @param string $value
     */
    public function setDescriptionAttribute($value)
    {
        $this->attributes['description'] = Purify::clean($value);
    }

    /**
     * Attribute Mutator: Category
     *
     * @param string $value
     */
    public function setCategoryAttribute($value)
    {
        $this->attributes['category'] = strip_tags($value);
    }

    /**
     * Attribute Mutator: Team Name
     *
     * @param string $value
     */
    public function setTeamNameAttribute($value)
    {
        $this->attributes['team_name'] = strip_tags($value);
    }

    /**
     * Attribute Mutator: Video URL
     *
     * @param string $value
     */
    public function setVideoUrlAttribute($value)
    {
        $this->attributes['video_url'] = filter_var($value, FILTER_SANITIZE_URL);
    }

    public function markAsPendingOrActivate($notify = true): void
    {
        $this->status = 'pending';
        $this->save();

        if (! empty($this->member_organizer_id && $this->memberOrganizer->is_verified)) {
            $this->activate($notify);
        }

        // always notify staff
        User::where('notify_fundraising_page_activated', '=', true)->get()->each(function ($user) {
            $user->mail(new \Ds\Mail\FundraisingPageActivated($this->getMergeTags()));
        });
    }

    /**
     * Activate a page
     *
     * @param bool $notify
     * @return void
     */
    public function activate($notify = true)
    {
        $this->status = 'active';
        $this->activated_date = fromLocal('now');
        $this->save();

        // trigger an email notification
        if ($notify && ! empty($this->member_organizer_id)) {
            // notify member
            if ($this->memberOrganizer->is_verified) {
                $this->memberOrganizer->notify('fundraising_page_activated', $this->getMergeTags());
            }
        }
    }

    /**
     * Report a page
     *
     * @param int $member_id
     * @param string $reason
     * @param bool $notify
     * @return void
     */
    public function report($member_id = null, $reason = null, $notify = true)
    {
        if (empty($member_id)) {
            $member_id = $this->member_organizer_id;
        }

        $report = new FundraisingPageReport([
            'reason' => $reason,
            'member_id' => $member_id,
            'reported_at' => Carbon::now(),
        ]);

        // set the status to active
        $this->reports()->save($report);

        // increment aggregates
        $this->updateAggregates();

        // trigger an email notification
        if ($notify && ! empty($this->member_organizer_id)) {
            // notify member
            $this->memberOrganizer->notify('fundraising_page_abuse', $this->getMergeTags([
                'page_report_reason' => $reason,
            ]));
        }

        // always notify staff
        User::where('notify_fundraising_page_abuse', '=', true)->get()->each(function ($user) use ($reason) {
            $user->mail(new \Ds\Mail\FundraisingPageAbuse($this->getMergeTags([
                'page_report_reason' => $reason,
            ])));
        });
    }

    /**
     * Cancel a page
     *
     * @param bool $notify
     * @return void
     */
    public function cancel($notify = true)
    {
        // set the status to daactivated
        $this->status = 'cancelled';
        $this->save();

        // trigger an email notification
        if ($notify && ! empty($this->member_organizer_id)) {
            // notify member
            $this->memberOrganizer->notify('fundraising_page_cancelled', $this->getMergeTags());
        }
    }

    /**
     * Suspend a page
     *
     * @param bool $notify
     * @return void
     */
    public function suspend($notify = true)
    {
        // set the status to daactivated
        $this->status = 'suspended';
        $this->save();

        // trigger an email notification
        if ($notify && ! empty($this->member_organizer_id)) {
            // notify member
            $this->memberOrganizer->notify('fundraising_page_suspended', $this->getMergeTags());
        }
    }

    public function isViewable(): bool
    {
        if (user()->can('fundraisingpages.edit')) {
            return true;
        }

        if (member('id') === $this->memberOrganizer->id) {
            return true;
        }

        return $this->status === 'active'
            && $this->memberOrganizer->isVerified;
    }

    /**
     * Update fundraising page aggregates.
     *
     * TO-DO
     * - add team aggregate totals
     *
     * @return void
     */
    public function updateAggregates()
    {
        $stats = DB::query()
            ->select([
                DB::raw('sum(donation_count) as donation_count'),
                DB::raw('sum(amount_raised) as amount_raised'),
            ])->fromSub(
                DB::table('productorderitem as i')
                    ->select([
                        DB::raw('count(i.id) as donation_count'),
                        DB::raw('sum(i.qty*i.price*o.functional_exchange_rate) as amount_raised'),
                    ])->join('productorder as o', 'o.id', '=', 'i.productorderid')
                    ->where('i.fundraising_page_id', $this->id)
                    ->whereNotNull('o.confirmationdatetime')
                    ->whereNull('o.refunded_at')
                    ->whereNull('o.deleted_at')
                    ->unionAll(
                        DB::table('transactions as t')
                            ->select([
                                DB::raw('count(t.id) as donation_count'),
                                DB::raw('sum((t.amt-t.tax_amt-t.dcc_amount-t.shipping_amt)*t.functional_exchange_rate) as amount_raised'),
                            ])->join('recurring_payment_profiles as r', 'r.id', '=', 't.recurring_payment_profile_id')
                            ->join('productorderitem as i', 'i.id', '=', 'r.productorderitem_id')
                            ->where('i.fundraising_page_id', $this->id)
                            ->where('t.payment_status', 'Completed')
                            ->whereNull('t.refunded_at')
                    ),
                'aggregates'
            )->first();

        $raised = money($stats->amount_raised, $this->functional_currency_code)
            ->toCurrency($this->currency_code)
            ->getAmount();

        $this->amount_raised = $raised + ($this->amount_raised_offset ?? 0);
        $this->donation_count = $stats->donation_count + ($this->donation_count_offset ?? 0);

        $teamCurrencyStats = $this->teamMembers()
            ->select([
                DB::raw('sum(donation_count) as donation_count'),
                DB::raw('sum(amount_raised) as amount_raised'),
                'currency_code',
            ])->groupBy('currency_code')
            ->get();

        foreach ($teamCurrencyStats as $currencyStats) {
            $this->amount_raised += money($currencyStats->amount_raised, $currencyStats->currency_code)
                ->toCurrency($this->currency_code)
                ->getAmount();

            $this->donation_count += $currencyStats->donation_count;
        }

        $this->report_count = $this->reports->count();
        $this->save();

        optional($this->teamFundraisingPage)->updateAggregates();
    }

    /**
     * Update all fundraising pages.
     *
     * @return void
     */
    public static function updateAllAggregates()
    {
        self::chunk(100, function ($pages) {
            foreach ($pages as $page) {
                $page->updateAggregates();
            }
        });
    }

    public function regenerateTeamJoinCode(): void
    {
        $this->team_join_code = strtoupper(Str::random(4));
    }

    /**
     * Get the merge tags for this fundraising page
     * when sending emails.
     *
     * @param array $additional_tags
     * @return array
     */
    public function getMergeTags(array $additional_tags = null)
    {
        $tags = [
            'page_admin_url' => secure_site_url(route('backend.fundraising-pages.view', $this->id, false)),
            'page_name' => $this->title,
            'page_url' => $this->absolute_url,
            'page_deadline' => $this->goal_deadline,
            'page_goal' => (string) money($this->goal_amount, $this->currency_code),
            'page_amount_raised' => (string) money($this->amount_raised, $this->currency_code),
            'page_goal_amount_remaining' => (string) money($this->remaining_to_raise, $this->currency_code),
            'page_suspend_url' => secure_site_url('/jpanel/fundraising-pages/' . $this->id . '/suspend'),
            'page_author' => $this->memberOrganizer->display_name,
            'page_author_verified_status' => $this->memberOrganizer->verified_status,
            'page_author_verified_status_is_pending' => $this->memberOrganizer->isPending || $this->memberOrganizer->isUnverified,
            'page_author_first_name' => $this->memberOrganizer->first_name,
            'page_author_url' => secure_site_url(route('backend.member.edit', $this->member_organizer_id, false)),
            'page_edit_url' => $this->absolute_edit_url,
            'page_report_reason' => null,
            'page_report_count' => $this->report_count,
        ];

        $tags += global_merge_tags();

        if (is_array($additional_tags)) {
            $tags = $additional_tags + $tags;
        }

        return $tags;
    }

    /**
     * Create a safe url from a page title.
     *
     * @param string $page_title
     * @param int $this_id
     * @param int $iteration
     * @return string
     */
    public static function createUniqueUrl($page_title, $this_id = null, $iteration = 0)
    {
        $url = Str::slug($page_title, '-') . (($iteration > 0) ? ('-' . $iteration) : '');

        $exclude_this_id = function ($q) use ($this_id) {
            if (isset($this_id)) {
                $q->where('id', '!=', $this_id);
            }
        };

        if (self::where('url', $url)->where($exclude_this_id)->count() == 0) {
            return $url;
        }

        return self::createUniqueUrl($page_title, $this_id, ++$iteration);
    }

    /**
     * Liquid representation of model.
     */
    public function toLiquid()
    {
        return \Ds\Domain\Theming\Liquid\Drop::factory($this, 'FundraisingPage');
    }
}
