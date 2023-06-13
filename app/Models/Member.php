<?php

namespace Ds\Models;

use Carbon\Carbon;
use Ds\Domain\Commerce\Enums\ContributionPaymentType;
use Ds\Domain\Shared\Exceptions\DisclosableException;
use Ds\Domain\Shared\Exceptions\MessageException;
use Ds\Domain\Sponsorship\Models\Sponsor;
use Ds\Domain\Sponsorship\Models\Sponsorship;
use Ds\Domain\Theming\Liquid\Liquidable;
use Ds\Eloquent\Hashids;
use Ds\Eloquent\HasMetadata;
use Ds\Eloquent\Metadatable;
use Ds\Eloquent\Permissions;
use Ds\Eloquent\Spammable;
use Ds\Eloquent\Userstamps;
use Ds\Enums\MemberOptinAction;
use Ds\Enums\RecurringPaymentProfileStatus;
use Ds\Enums\Supporters\SupporterVerifiedStatus;
use Ds\Illuminate\Auth\Autologinable as AutologinableContract;
use Ds\Illuminate\Auth\Concerns\Autologinable;
use Ds\Illuminate\Database\Eloquent\Auditable;
use Ds\Illuminate\Database\Eloquent\Factories\HasFactory;
use Ds\Illuminate\Database\Eloquent\HasAuditing;
use Ds\Illuminate\Database\Eloquent\Model;
use Ds\Illuminate\Database\Eloquent\Redactor;
use Ds\Models\Observers\MemberObserver;
use Ds\Models\Traits\HasComments;
use Ds\Models\Traits\HasExternalReferences;
use Ds\Models\Traits\HasSocialIdentities;
use Ds\Models\Traits\HasUserDefinedFields;
use Ds\Repositories\MemberOptinLogRepository;
use Ds\Services\DonorPerfectService;
use Ds\Services\MemberService;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use libphonenumber\NumberParseException;
use Propaganistas\LaravelPhone\PhoneNumber;
use Spatie\Url\Url;
use Throwable;

class Member extends Model implements Auditable, AuthenticatableContract, AutologinableContract, Liquidable, Metadatable
{
    use Authenticatable;
    use Autologinable;
    use HasAuditing;
    use HasComments;
    use HasExternalReferences;
    use HasFactory;
    use Hashids;
    use HasMetadata;
    use HasSocialIdentities;
    use HasUserDefinedFields;
    use Permissions;
    use Spammable;
    use Userstamps;

    /**
     * The name of the "deleted at" column.
     *
     * @var string
     */
    const DELETED_AT = 'is_active';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'member';

    /**
     * Attributes to exclude from the Audit.
     *
     * @var array
     */
    protected $auditExclude = [
        'created_by',
        'updated_by',
    ];

    /**
     * Attribute modifiers.
     *
     * @var array
     */
    protected $attributeModifiers = [
        'password' => Redactor::class,
        'remember_token' => Redactor::class,
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var string[]|bool
     */
    protected $guarded = [
        'id',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_opt_in_deprecated' => 'boolean',
        'is_active' => 'boolean',
        'is_spam' => 'boolean',
        'donor_id' => 'integer',
        'force_password_reset' => 'boolean',
        'sms_verified' => 'boolean',
        'sync_status' => 'integer',
        'membership_expires_on' => 'date',
        'nps' => 'integer',
        'should_display_badge' => 'boolean',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'display_bill_address',
        'display_bill_phone',
        'email_opt_in',
        'fa_icon',
        'referral_code',

        // legacy volt support
        'is_membership_expired',
        'membership',
        'membership_expires_on',
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        self::observe(new MemberObserver);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class)
            ->paid();
    }

    public function firstOrder(): HasOne
    {
        return $this->hasOne(Order::class)
            ->paid()
            ->orderBy('confirmationdatetime', 'asc');
    }

    public function lastOrder(): HasOne
    {
        return $this->hasOne(Order::class)
            ->paid()
            ->orderBy('confirmationdatetime', 'desc');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'source_account_id');
    }

    public function lastPayment(): HasOne
    {
        return $this->hasOne(Payment::class, 'source_account_id')
            ->withSpam()
            ->where('status', 'succeeded')
            ->orderBy('created_at', 'desc');
    }

    public function accountType(): BelongsTo
    {
        return $this->belongsTo(AccountType::class)
            ->withTrashed();
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Membership::class, 'group_account', 'account_id', 'group_id')
            ->using(GroupAccount::class)
            ->withPivot([
                'id',
                'group_account_timespan_id',
                'end_date',
                'start_date',
                'order_item_id',
                'source',
                'end_reason',
            ]);
    }

    public function groupAccountTimespans()
    {
        return $this->belongsToMany(Membership::class, 'group_account_timespan', 'account_id', 'group_id')
            ->using(GroupAccountTimespan::class)
            ->withPivot([
                'id',
                'end_date',
                'start_date',
            ]);
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'referred_by');
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(Member::class, 'referred_by');
    }

    public function activeGroups(): Collection
    {
        return $this->groups
            ->where('pivot.is_active', true);
    }

    /**
     * Collection: A collection of active group promocodes.
     *
     * @return \Illuminate\Support\Collection
     */
    public function activeGroupPromocodes()
    {
        $codes = collect([]);

        $this->activeGroups()->pluck('promocodes')->each(function ($promocodes) use (&$codes) {
            $promocodes->each(function ($promocode) use (&$codes) {
                if (! $codes->contains('code', $promocode->code)) {
                    $codes->push($promocode);
                }
            });
        });

        return $codes;
    }

    public function sponsors(): HasMany
    {
        return $this->hasMany(Sponsor::class);
    }

    /**
     * Only active sponsorships
     */
    public function sponsorships(): BelongsToMany
    {
        return $this->allSponsorships()
            ->whereNull('sponsors.ended_at')
            ->orWhere('sponsors.ended_at', '>', Carbon::now());
    }

    public function allSponsorships(): BelongsToMany
    {
        return $this->belongsToMany(Sponsorship::class, 'sponsors')
            ->whereNull('sponsors.deleted_at')
            ->withPivot('started_at', 'ended_at', 'ended_reason');
    }

    public function loginAuditLogs(): HasMany
    {
        return $this->hasMany(MemberLogin::class)
            ->orderBy('login_at', 'desc');
    }

    public function ownLoginLogs(): HasMany
    {
        return $this->loginAuditLogs()
            ->whereNull('impersonated_by');
    }

    public function paymentMethods(): HasMany
    {
        return $this->hasMany(PaymentMethod::class);
    }

    public function defaultPaymentMethod(): HasOne
    {
        return $this->hasOne(PaymentMethod::class, 'member_id')
            ->defaultPaymentMethod();
    }

    public function recurringPaymentProfiles(): HasMany
    {
        return $this->hasMany(RecurringPaymentProfile::class);
    }

    public function activeRecurringPaymentProfiles(): HasMany
    {
        return $this->hasMany(RecurringPaymentProfile::class)
            ->active();
    }

    public function chargeableRpps(): HasMany
    {
        return $this->recurringPaymentProfiles()
            ->chargeable();
    }

    public function taxReceipts(): HasMany
    {
        return $this->hasMany(TaxReceipt::class, 'account_id');
    }

    public function transactions(): HasManyThrough
    {
        return $this->hasManyThrough(Transaction::class, RecurringPaymentProfile::class)
            ->orderBy('order_time', 'desc');
    }

    public function fundraisingPages(): HasMany
    {
        return $this->hasMany(FundraisingPage::class, 'member_organizer_id');
    }

    public function pledges(): HasMany
    {
        return $this->hasMany(Pledge::class, 'account_id');
    }

    /**
     * Relationship: MemberOptinLogs
     */
    public function optinLogs(): HasMany
    {
        return $this->hasMany(MemberOptinLog::class);
    }

    public static function spammableByDefault(): bool
    {
        return false;
    }

    /**
     * Scope: Active members
     *
     * @param \Illuminate\Database\Query\Builder $query
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Inactive members
     *
     * @param \Illuminate\Database\Query\Builder $query
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope: Bill Phone E164
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param int|string|null $phoneNumber
     * @param string|null $countryCode
     *
     * @throws \libphonenumber\NumberParseException
     */
    public function scopeBillPhoneE164($query, $phoneNumber, $countryCode = null)
    {
        try {
            $phone = PhoneNumber::make(
                $phoneNumber,
                $countryCode ?? sys_get('default_country')
            );

            $query->where('bill_phone_e164', $phone->formatE164());
        } catch (NumberParseException $e) {
            $query->whereRaw('1=0');
        }
    }

    /**
     * Scope: SMS Verified
     *
     * @param \Illuminate\Database\Query\Builder $query
     */
    public function scopeSmsVerified($query, $phoneNumber)
    {
        $query->where('sms_verified', true);
    }

    public function scopeDenied($query): void
    {
        $query->where('verified_status', SupporterVerifiedStatus::DENIED);
    }

    public function scopePending($query): void
    {
        $query->where('verified_status', SupporterVerifiedStatus::PENDING);
    }

    public function scopeUnverified($query): void
    {
        $query->whereNull('verified_status');
    }

    public function scopeVerified($query): void
    {
        $query->where('verified_status', SupporterVerifiedStatus::VERIFIED);
    }

    /**
     * Attribute Accessor: Account Id
     *
     * @return string
     */
    public function getAccountIdAttribute()
    {
        return 'acc_' . app('hashids')->encode($this->id);
    }

    /**
     * `email_opt_in` accessor for backward compatibility.
     */
    public function getEmailOptInAttribute(): bool
    {
        // Optin is false for a new Member model.
        if (! $this->getKey()) {
            return false;
        }

        /** @var \Ds\Repositories\MemberOptinLogRepository */
        $memberOptinLogRepository = app(MemberOptinLogRepository::class);
        $memberOptinLog = $memberOptinLogRepository->getLastLogFromMember($this->getKey());

        return $memberOptinLog
            && $memberOptinLog->action === MemberOptinAction::OPTIN;
    }

    /**
     * `email_opt_in` mutator for backward compatibility.
     */
    public function setEmailOptInAttribute($value): void
    {
        $optin = (bool) $value;

        /** @var \Ds\Services\MemberService */
        $memberService = app(MemberService::class, ['member' => $this]);

        if ($optin) {
            $memberService->optin();

            return;
        }

        $memberService->optout('User opted out in the Administration area by ' . user('full_name'));
    }

    /**
     * Get the full display name of the member.
     *
     * @return string
     */
    public function getFaIconAttribute()
    {
        return ($this->accountType && $this->accountType->is_organization) ? 'fa-building' : 'fa-user';
    }

    public function getInitialsAttribute(): ?string
    {
        return Str::initials($this->display_name);
    }

    public function getGravatarAttribute(): string
    {
        return gravatar($this->email);
    }

    /**
     * Get the full display billing address of the member.
     *
     * @return string
     */
    public function getDisplayBillAddressAttribute()
    {
        return address_format($this->bill_address_01, $this->bill_address_02, $this->bill_city, $this->bill_state, $this->bill_zip, $this->bill_country);
    }

    /**
     * Attribute Accessor: Salutation
     *
     * @return string
     */
    public function getSalutationAttribute()
    {
        if ($this->title && $this->last_name) {
            return $this->title . ' ' . $this->last_name;
        }

        return $this->first_name ?: null;
    }

    /**
     * Get full email address formatted
     *
     * @return string
     */
    public function getEmailAddressFormattedAttribute()
    {
        return $this->first_name . ' ' . $this->last_name . ' <' . $this->email . '>';
    }

    /**
     * Get the full display billing phone of the member.
     *
     * @return string
     */
    public function getDisplayBillPhoneAttribute()
    {
        return phone_format($this->bill_phone);
    }

    /**
     * Attribute Mutator: Bill Phone
     *
     * @param string $value
     */
    public function setBillPhoneAttribute($value)
    {
        $this->attributes['bill_phone'] = $value;

        try {
            $phone = PhoneNumber::make(
                $this->bill_phone,
                $this->bill_country ?? sys_get('default_country')
            );

            $this->bill_phone_e164 = $phone->formatE164();
        } catch (NumberParseException $e) {
            $this->bill_phone_e164 = null;
        }

        if ($this->isDirty('bill_phone_e164')) {
            $this->sms_verified = false;
        }
    }

    /**
     * Names of all sponsorships as a string.
     *
     * @return string
     */
    public function getSponsorshipNamesAttribute()
    {
        $names = [];

        foreach ($this->sponsorships as $sponsorship) {
            $names[] = $sponsorship->full_name . (($sponsorship->reference_number) ? ' (' . $sponsorship->reference_number . ')' : '');
        }

        return implode(', ', $names);
    }

    /**
     * Names of all sponsorships as an HTML string.
     *
     * @return string
     */
    public function getSponsorshipNamesHtmlAttribute()
    {
        $names = [];
        foreach ($this->sponsorships as $sponsorship) {
            $names[] = $sponsorship->html_button;
        }

        return implode(' ', $names);
    }

    public function getIsDeniedAttribute(): bool
    {
        if (! sys_get('bool:fundraising_pages_requires_verify')) {
            return false;
        }

        return $this->verified_status === SupporterVerifiedStatus::DENIED;
    }

    public function getIsPendingAttribute(): bool
    {
        if (! sys_get('bool:fundraising_pages_requires_verify')) {
            return false;
        }

        return $this->verified_status === SupporterVerifiedStatus::PENDING;
    }

    public function getIsUnverifiedAttribute(): bool
    {
        if (! sys_get('bool:fundraising_pages_requires_verify')) {
            return false;
        }

        return empty($this->verified_status);
    }

    public function getIsVerifiedAttribute(): bool
    {
        if (! sys_get('bool:fundraising_pages_requires_verify')) {
            return true;
        }

        return $this->verified_status === SupporterVerifiedStatus::VERIFIED;
    }

    /**
     * Mutator: notices
     *
     * For example "Missing a payment method"
     *
     * @return array
     */
    public function getNoticesAttribute()
    {
        $notices = [];

        $activeRppCount = $this->recurringPaymentProfiles()
            ->where('is_manual', false)
            ->where('is_locked', false)
            ->active()
            ->count();

        // if they have active payment profiles and no payment methods
        if ($activeRppCount > 0) {
            // no valid payment methods
            if ($this->paymentMethods()->goodStanding()->count() == 0) {
                $notices[] = [
                    'type' => 'payment_method_missing',
                    'severity' => 'warning',
                    'message' => 'Add a payment method so we can process your payments.',
                    'action' => 'Add Payment Method',
                    'action_url' => '/account/payment-methods/add',
                ];
            }

            // expiring payment methods
            if ($this->paymentMethods()->isInUse()->expiring()->count() > 0) {
                $notices[] = [
                    'type' => 'payment_method_expiring',
                    'severity' => 'warning',
                    'message' => 'One of your credit cards are about to expire.',
                    'action' => 'View Payment Methods',
                    'action_url' => '/account/payment-methods',
                ];
            }
        }

        // if they have active payment profiles and no payment methods
        if ($this->recurringPaymentProfiles()->where('status', RecurringPaymentProfileStatus::SUSPENDED)->count() > 0) {
            $notices[] = [
                'type' => 'paused_subscriptions',
                'severity' => 'warning',
                'message' => 'One of your recurring payments is paused.',
                'action' => 'View Payments',
                'action_url' => '/account/subscriptions',
            ];
        }

        // membership warnings
        if ($this->membership_expires_on) {
            if ($this->membership_expires_on->between(fromUtc('today -6 months'), fromUtc('today'))) {
                $notices[] = [
                    'type' => 'membership_expired',
                    'severity' => 'warning',
                    'message' => 'Your membership recently expired.',
                    'action' => 'View',
                    'action_url' => '/account/memberships',
                ];
            } elseif ($this->membership_expires_on->subDays(60)->isPast()) {
                $notices[] = [
                    'type' => 'membership_expiring',
                    'severity' => 'warning',
                    'message' => 'Your membership is about to expire.',
                    'action' => 'View',
                    'action_url' => '/account/memberships',
                ];
            }
        }

        return $notices;
    }

    /**
     * Mutator: nps_status
     *
     * Promoter 9-10 (0.9-1.0)
     * Passive 6-8 (0.6-0.8)
     * Detractors 0-5 (0-0.5)
     *
     * @param int $value
     * @return string|null
     */
    public function getNpsStatusAttribute($value)
    {
        switch (true) {
            case $this->nps >= 9:
                return 'promoter';
            case $this->nps >= 6:
                return 'passive';
            case $this->nps >= 1:
                return 'detractor';
            default:
                return null;
        }
    }

    /**
     * Mutator: nps
     *
     * Only allow numbers 1-10
     *
     * @param string $value
     * @return void
     */
    public function setNpsAttribute($value)
    {
        $value = (int) $value;
        if ($value >= 1 and $value <= 10) {
            $this->attributes['nps'] = $value;
        } else {
            $this->attributes['nps'] = null;
        }
    }

    /**
     * Mutator: ship_country
     *
     * Only allow setting the country if it matches the ISO code.
     *
     * @param string $value
     * @return void
     */
    public function setShipCountryAttribute($value)
    {
        $value = strtoupper(trim($value));
        if (array_key_exists($value, cart_countries())) {
            $this->attributes['ship_country'] = $value;
        } else {
            $this->attributes['ship_country'] = null;
        }
    }

    /**
     * Mutator: bill_country
     *
     * Only allow setting the country if it matches the ISO code.
     *
     * @param string $value
     * @return void
     */
    public function setBillCountryAttribute($value)
    {
        $value = strtoupper(trim($value));
        if (array_key_exists($value, cart_countries())) {
            $this->attributes['bill_country'] = $value;
        } else {
            $this->attributes['bill_country'] = null;
        }
    }

    /**
     * Mutator: email
     *
     * @param string $value
     * @return void
     */
    public function setEmailAttribute($value)
    {
        $value = trim($value);
        if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->attributes['email'] = $value;
        } else {
            $this->attributes['email'] = null;
        }
    }

    /**
     * Mutator: ship_email
     *
     * @param string $value
     * @return void
     */
    public function setShipEmailAttribute($value)
    {
        $value = trim($value);
        if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->attributes['ship_email'] = $value;
        } else {
            $this->attributes['ship_email'] = null;
        }
    }

    /**
     * Mutator: bill_email
     *
     * @param string $value
     * @return void
     */
    public function setBillEmailAttribute($value)
    {
        $value = trim($value);
        if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->attributes['bill_email'] = $value;
        } else {
            $this->attributes['bill_email'] = null;
        }
    }

    /**
     * Push this member into DPO as a donor.
     *
     * @return bool
     */
    public function pushToDpo()
    {
        try {
            return (bool) app(DonorPerfectService::class)->pushAccount($this);
        } catch (DisclosableException $e) {
            // do nothing
        } catch (Throwable $e) {
            notifyException($e);
        }

        return false;
    }

    /**
     * Ensure this record is pushed to dpo.
     *
     * @return bool
     */
    public function verifyDpo()
    {
        // if there is a donor id
        if ($this->donor_id) {
            // find the donor in DP
            $donor = dpo_request(sprintf('SELECT donor_id FROM dp WHERE donor_id = %d', $this->donor_id));

            // if they don't exist, create a donor
            if (count($donor) === 0) {
                return $this->pushToDpo();
            }

            return true;
        }

        // create a donor
        return $this->pushToDpo();
    }

    /**
     * Add this member to a group/membership
     *
     * @param \Ds\Models\Membership|int $group
     * @param string $start_date
     * @param string|\Ds\Models\OrderItem $source
     * @return \Ds\Models\GroupAccount
     */
    public function addGroup($group, $start_date = null, $source = null, $metadata = null): GroupAccount
    {
        // ensure $group is a Group model
        if (is_numeric($group)) {
            $group = Membership::find($group);
        } elseif (! is_a($group, Membership::class)) {
            throw new MessageException('Membership is an unrecognized datatype.');
        }

        $existingMembershipValidUntil = $this->groups
            ->where('id', $group->id)
            ->where('pivot.end_date', '>', fromLocal('today'))
            ->max('pivot.end_date');

        if ($group->starts_at) {
            $start_date = $group->starts_at;
        } elseif ($existingMembershipValidUntil) {
            $start_date = $existingMembershipValidUntil;
        }

        $start_date = fromLocal($start_date ?? 'today');

        // calculate end date
        $end_date = null;
        if ($group->days_to_expire) {
            $end_date = $start_date->copy()->addDays($group->days_to_expire);
        }

        // attach account to group
        $groupAccount = new GroupAccount;
        $groupAccount->account_id = $this->id;
        $groupAccount->group_id = $group->id;
        $groupAccount->start_date = $start_date;
        $groupAccount->end_date = $end_date;
        $groupAccount->order_item_id = is_a($source, OrderItem::class) ? $source->id : null;
        $groupAccount->source = is_string($source) ? $source : null;

        if (is_array($metadata) && ! empty($metadata)) {
            $groupAccount->metadata($metadata);
        }

        $groupAccount->save();

        event(new \Ds\Events\AccountAddedToGroup($groupAccount));

        return $groupAccount;
    }

    /**
     * Add this member to a group/membership
     *
     * @param \Ds\Models\Membership|int $group
     * @param string $start_date
     * @param string|\Ds\Models\OrderItem $source
     * @return \Ds\Models\GroupAccount
     */
    public function addUniqueGroup($group, $start_date = null, $source = null, $metadata = null): GroupAccount
    {
        // ensure $group is a Group model
        if (is_numeric($group)) {
            $group = Membership::find($group);
        } elseif (! is_a($group, Membership::class)) {
            throw new MessageException('Membership is an unrecognized datatype.');
        }

        // make sure the link hasn't already been made
        if (is_a($source, OrderItem::class)) {
            if (
                GroupAccount::whereAccountId($this->id)
                    ->where('order_item_id', $source->id)
                    ->count() > 0
            ) {
                throw new MessageException('The supporter is already linked to the contribution.');
            }
        }

        // default value for start date (today)
        $start_date = fromLocal($start_date ?? 'today');

        // look-up an existing GroupAccount assignment
        $groupAccount = $this->groups
            ->where('id', $group->id)
            ->where('pivot.is_active', true)
            ->first();

        if ($groupAccount) {
            return $groupAccount->pivot;
        }

        return $this->addGroup($group, $start_date, $source, $metadata);
    }

    /**
     * Remove membership to this member
     *
     * @param \Ds\Models\Membership $membership
     * @param string $end_date
     * @param string $reason
     * @return void
     */
    /* public function removeMembership (Membership $membership, $end_date = null, $reason = null) {

        // default value for start date (today)
        $end_date = $end_date ?? fromUtc('today');

        $this->membership()
            ->where('id', $membership->id)
            ->orderBy('start_date', 'desc')
            ->first();

        // attach the membership
        $this->memberships()->attach($membership->id, [
            'start_date'    => $start_date,
            'end_date'      => $membership->starts_at ? $membership->starts_at->addDays($membership->days_to_expire) : $start_date->addDays($membership->days_to_expire),
            'end_reason'    => $reason
        ]);
    } */

    /**
     * Apply the default promotions an order.
     *
     * @param \Ds\Models\Order $order
     * @return void
     */
    public function applyMembershipPromocodes(Order $order)
    {
        // filter out invalid promos, also keeping non membership related ones.
        $promo_codes = $this->activeGroupPromocodes()
            ->merge($order->promoCodes)
            ->unique('code')
            ->reject(function ($promo) use ($order) {
                try {
                    \Ds\Models\PromoCode::validate($promo->code, $order->is_pos, $order->billingemail, $this);
                } catch (\Exception $e) {
                    return true;
                }

                return false;
            });

        $order->applyPromos($promo_codes);
    }

    /**
     * Create a new payment method using the defaults from this member.
     *
     * @return \Ds\Models\PaymentMethod
     */
    public function newPaymentMethod()
    {
        $p = new PaymentMethod;
        $p->billing_first_name = $this->bill_first_name;
        $p->billing_last_name = $this->bill_last_name;
        $p->billing_address1 = $this->bill_address_01;
        $p->billing_address2 = $this->bill_address_02;
        $p->billing_city = $this->bill_city;
        $p->billing_state = $this->bill_state;
        $p->billing_postal = $this->bill_zip;
        $p->billing_country = $this->bill_country;
        $p->billing_phone = $this->bill_phone;

        return $p;
    }

    /**
     * Send a notification to the member
     *
     * @param \Ds\Models\Email|string $email
     * @param array $params
     * @param bool $includeDefaultParams
     * @return bool
     */
    public function notify($email, $params = [], $includeDefaultParams = true)
    {
        // if a string was passed in
        if (is_string($email)) {
            $email = Email::where('is_active', 1)->where('type', $email)->first();
        }

        // bail if the email template doesn't exist
        if (! isset($email) || ! is_a($email, Email::class)) {
            return false;
        }

        $to = $this->email_address_formatted;

        if ($includeDefaultParams) {
            $params = array_merge($params, [
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'email' => $this->email,
                'bill_first_name' => $this->bill_first_name,
                'bill_last_name' => $this->bill_last_name,
                'bill_address_01' => $this->bill_address_01,
                'bill_address_02' => $this->bill_address_02,
                'bill_city' => $this->bill_city,
                'bill_state' => $this->bill_state,
                'bill_zip' => $this->bill_zip,
                'bill_phone' => $this->bill_phone,
                'bill_email' => $this->bill_email,
                'ship_first_name' => $this->ship_first_name,
                'ship_last_name' => $this->ship_last_name,
                'ship_address_01' => $this->ship_address_01,
                'ship_address_02' => $this->ship_address_02,
                'ship_city' => $this->ship_city,
                'ship_state' => $this->ship_state,
                'ship_zip' => $this->ship_zip,
                'ship_phone' => $this->ship_phone,
                'ship_email' => $this->ship_email,
                'referral_link' => $this->getShareableLink('/'),
            ]);
        }

        return $email->send($to, $params);
    }

    /**
     * Register a new supporter
     *
     * @param array $attributes
     * @param bool $auto_login
     * @return \Ds\Models\Member
     */
    public static function register($attributes, $auto_login = false)
    {
        // ensure email is unique
        if ($attributes['email'] && self::where('email', $attributes['email'])->count() > 0) {
            throw new MessageException("Cannot register supporter. '" . $attributes['email'] . "' is already in use.");
        }

        // auto-fill first name, last name and email across billing/shipping
        if (! isset($attributes['bill_first_name'])) {
            $attributes['bill_first_name'] = $attributes['first_name'];
        }
        if (! isset($attributes['bill_last_name'])) {
            $attributes['bill_last_name'] = $attributes['last_name'];
        }
        if (! isset($attributes['bill_email'])) {
            $attributes['bill_email'] = $attributes['email'];
        }
        if (! isset($attributes['ship_first_name'])) {
            $attributes['ship_first_name'] = $attributes['first_name'];
        }
        if (! isset($attributes['ship_last_name'])) {
            $attributes['ship_last_name'] = $attributes['last_name'];
        }
        if (! isset($attributes['ship_email'])) {
            $attributes['ship_email'] = $attributes['email'];
        }

        // create a new supporter
        $member = new self($attributes);
        $member->save();

        // push to dpo (if it isn't already pushed to DPO)
        if (! $member->donor_id && sys_get('allow_account_users_to_update_donor') == '1') {
            $member->pushToDpo();
        }

        // trigger AccountWasRegistered event IF there is a
        // password on the member (meaning it is a new account)
        if ($member->password) {
            event(new \Ds\Events\AccountWasRegistered($member));
        }

        // auto login
        if ($auto_login) {
            member_login_with_id($member->id);
        }

        // return the member
        return $member;
    }

    /**
     * Set display_name attribute
     * (used from observer & in a migration)
     */
    public function setDisplayName()
    {
        if ($this->accountType && $this->accountType->is_organization && $this->bill_organization_name) {
            $this->display_name = trim($this->bill_organization_name);
        } else {
            $this->display_name = trim($this->first_name . ' ' . $this->last_name);
        }

        if (empty($this->display_name)) {
            $this->display_name = $this->email ?? $this->bill_email;
        }
    }

    /**
     * Set all display_name's
     */
    public static function setAllDisplayNames()
    {
        foreach (static::cursor() as $m) {
            $m->setDisplayName();
            $m->save();
        }
    }

    /**
     * Liquid representation of model.
     */
    public function toLiquid()
    {
        return \Ds\Domain\Theming\Liquid\Drop::factory($this, 'Account');
    }

    /**
     * Set all display_name's
     */
    public static function findCloseMatchesTo($model, $except_id = null)
    {
        if (is_a($model, Member::class)) {
            $search = [
                'first_name' => $model->first_name,
                'last_name' => $model->last_name,
                'organization' => $model->bill_organization_name,
                'email' => $model->email,
                'zip' => $model->bill_zip,
            ];
        } elseif (is_a($model, Order::class)) {
            $search = [
                'first_name' => $model->billing_first_name,
                'last_name' => $model->billing_last_name,
                'organization' => $model->billing_organization_name,
                'email' => $model->billingemail,
                'zip' => $model->billingzip,
            ];
        }

        // if there is nothing to match on, return no results
        if (! ($search['first_name'] || $search['last_name'] || $search['organization'] || $search['email'] || $search['zip'])) {
            return collect([]);
        }

        if ($search['zip']) {
            $search['zip'] = preg_replace('/[^A-Za-z0-9]/', '', $search['zip']);
        }

        $results = self::active();

        $results->where(function ($qry) use ($search) {
            if ($search['first_name'] && $search['last_name']) {
                $qry->orWhere('display_name', 'like', '%' . $search['first_name'] . ' ' . $search['last_name'] . '%');
            } elseif ($search['first_name']) {
                $qry->orWhere('first_name', 'like', $search['first_name'] . '%');
            } elseif ($search['last_name']) {
                $qry->orWhere('last_name', 'like', $search['last_name'] . '%');
            }

            if ($search['email']) {
                $qry->orWhere('email', 'like', $search['email']);
            }

            if ($search['organization']) {
                $qry->orWhere('bill_organization_name', 'like', $search['organization'] . '%');
            }

            if ($search['last_name'] && $search['zip']) {
                $qry->orWhere(function ($qry) use ($search) {
                    $qry->where(DB::raw("replace(replace(member.bill_zip, '-', ''), ' ', '')"), 'like', $search['zip'] . '%')
                        ->where('last_name', 'like', $search['last_name'] . '%');
                });
            }
        });

        if ($except_id) {
            $results->where('id', '!=', $except_id);
        }

        return $results->take(25)->get();
    }

    /**
     * Find the closes match to the model passed in
     *
     * @param array $contact
     * @return Member|null
     */
    public static function findClosestMatchTo(array $contact)
    {
        $contact += [
            'first_name' => null,
            'last_name' => null,
            'email' => null,
            'zip' => null,
            'donor_id' => null,
        ];

        // find one with the exact same donor id
        if ($contact['donor_id']) {
            $donor_match = self::active()
                ->where('donor_id', $contact['donor_id'])
                ->orderBy('created_at', 'desc')
                ->first();

            if ($donor_match) {
                return $donor_match;
            }
        }

        // find one with the exact same email
        if ($contact['email']) {
            $email_match = self::active()
                ->where('email', $contact['email'])
                ->orderBy('created_at', 'desc')
                ->first();

            if ($email_match) {
                return $email_match;
            }
        }

        // find one with the exact same name and zip
        if (
            $contact['first_name']
            && $contact['last_name']
            && $contact['zip']
        ) {
            $name_address_match = self::active()
                ->where('first_name', 'like', $contact['first_name'])
                ->where('last_name', 'like', $contact['last_name'])
                ->where('bill_zip', 'like', $contact['zip'] . '%')
                ->orderBy('created_at', 'desc')
                ->first();

            if ($name_address_match) {
                return $name_address_match;
            }
        }

        return null;
    }

    /**
     * Get a query for all tax receipts belonging to this member.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function issuedTaxReceipts()
    {
        return $this->taxReceipts()->where('status', 'issued');
    }

    /**
     * Create a member from the billing data in an order.
     *
     * !! NOTE !!
     * - If there is already a member associated with this order, it will not create a new member
     * - If there is a matching account by 'name and zip' or 'email', it will link that member id
     * - Otherwise, a new account will be created
     *   - If an account with the same email exists, this will still create a member but it will leave EMAIL BLANK
     *
     * @param \Ds\Models\Order $order
     * @param string $password
     * @param bool $force_create_member
     * @return \Ds\Models\Member|null
     */
    public static function createFromOrder(Order $order, $password = null, $force_create_member = false)
    {
        // if we're NOT forced to create a member,
        // lets try finding an existing member
        if (! $force_create_member) {
            $closest_match = self::findClosestMatchTo([
                'first_name' => $order->billing_first_name,
                'last_name' => $order->billing_last_name,
                'email' => $order->billingemail,
                'zip' => $order->billingzip,
                'donor_id' => $order->alt_contact_id,
            ]);

            // return the closest match
            if ($closest_match) {
                return $closest_match;
            }
        }

        // if there isn't enough to create a member, bail
        $hasRequiredContactInfo = $order->billing_first_name || $order->billing_last_name || $order->billingemail || $order->billing_organization_name;
        $usingPayPalForFundraisingForm = $order->payment_type === ContributionPaymentType::PAYPAL && $order->isForFundraisingForm();

        if (! $hasRequiredContactInfo && ! $usingPayPalForFundraisingForm) {
            // throw an error if we're trying to FORCE the creation of a member
            if ($force_create_member) {
                throw new MessageException("There isn't enough data to create an account. Add at least a name or email to the contribution.");
            }

            // otherwise, just return null
            return null;
        }

        // create member and copy billing info of order over
        $member = new self;
        $member->title = $order->billing_title;
        $member->first_name = $order->billing_first_name;
        $member->last_name = $order->billing_last_name;
        $member->ship_title = $order->shipping_title;
        $member->ship_first_name = $order->shipping_first_name;
        $member->ship_last_name = $order->shipping_last_name;
        $member->ship_organization_name = $order->shipping_organization_name;
        $member->ship_email = $order->shipemail;
        $member->ship_address_01 = $order->shipaddress1;
        $member->ship_address_02 = $order->shipaddress2;
        $member->ship_city = $order->shipcity;
        $member->ship_state = $order->shipstate;
        $member->ship_zip = $order->shipzip;
        $member->ship_country = $order->shipcountry;
        $member->ship_phone = $order->shipphone;
        $member->bill_title = $order->billing_title;
        $member->bill_first_name = $order->billing_first_name;
        $member->bill_last_name = $order->billing_last_name;
        $member->bill_organization_name = $order->billing_organization_name;
        $member->bill_email = $order->billingemail;
        $member->bill_address_01 = $order->billingaddress1;
        $member->bill_address_02 = $order->billingaddress2;
        $member->bill_city = $order->billingcity;
        $member->bill_state = $order->billingstate;
        $member->bill_zip = $order->billingzip;
        $member->bill_country = $order->billingcountry;
        $member->bill_phone = $order->billingphone;
        $member->donor_id = $order->alt_contact_id;
        $member->account_type_id = $order->account_type_id;
        $member->referral_source = $order->referral_source;

        // set login info
        // only if email is unique
        if (self::where('email', $order->billingemail)->count() === 0) {
            // set email
            $member->email = $order->billingemail;

            // if a password was provided, set it & hash it
            if ($password) {
                $member->password = bcrypt($password);
            }
        }

        // save member
        $member->created_at = Carbon::now();
        $member->updated_at = Carbon::now();

        // we always want the created date to match the order (from a end-user standpoint, the donor
        // was "created" or "first-seen" on the date the order was placed)
        if ($order->createddatetime) {
            $member->created_at = $order->createddatetime->copy();
        }

        $member->save(['timestamps' => false]);

        return $member;
    }

    /**
     * Handle an autologin.
     */
    public function autologin()
    {
        member_login_with_id($this->id);
    }

    /**
     * Get the default URL to use after an autologin
     * has occurred.
     *
     * @return string
     */
    public function getAutologinDefaultUrl()
    {
        return sys_get('login_success_contingency_url');
    }

    /**
     * Get a list of historical events for this member
     *
     * - list of all logins
     * - list of changes to rpp
     * - list of changes to self
     */
    public function getHistory()
    {
        // logins
        $history = $this->loginAuditLogs()
            ->select([
                DB::raw('member_login.id as sequence'),
                DB::raw('login_at as occured_at'),
                DB::raw("'Logged in' as description"),
                DB::raw('ip'),
                DB::raw('user_agent'),
                DB::raw('null as metadata'),
                DB::raw('null as url_reference'),
                DB::raw("(case when user.id then concat(user.firstname,' ',user.lastname) else null end) as user_reference"),
            ])->leftJoin('user', 'user.id', '=', 'member_login.impersonated_by');

        // account updates
        $history->union($this->audits()
            ->select([
                DB::raw('audits.id as sequence'),
                DB::raw('created_at as occured_at'),
                DB::raw("concat('Account ',event) as description"),
                DB::raw('ip_address'),
                DB::raw('user_agent'),
                DB::raw('new_values as metadata'),
                DB::raw('url as url_reference'),
                DB::raw("(case when user.id then concat(user.firstname,' ',user.lastname) else null end) as user_reference"),
            ])->leftJoin('user', function ($join) {
                $join->on('user.id', '=', 'audits.user_id')
                    ->where('audits.user_type', '=', User::class);
            }));

        // if fundraising pages
        if ($this->fundraisingPages->count() > 0) {
            $history->union(DB::table('audits')
                ->select([
                    DB::raw('audits.id as sequence'),
                    DB::raw('created_at as occured_at'),
                    DB::raw("concat('Fundraising Page ',event) as description"),
                    DB::raw('ip_address'),
                    DB::raw('user_agent'),
                    DB::raw('new_values as metadata'),
                    DB::raw('url as url_reference'),
                    DB::raw("(case when user.id then concat(user.firstname,' ',user.lastname) else null end) as user_reference"),
                ])->leftJoin('user', function ($join) {
                    $join->on('user.id', '=', 'audits.user_id')
                        ->where('audits.user_type', '=', User::class);
                })->where('auditable_type', FundraisingPage::class)
                ->whereIn('auditable_id', $this->fundraisingPages->pluck('id')));
        }

        // if payment methods
        if ($this->paymentMethods->count() > 0) {
            $history->union(DB::table('audits')
                ->select([
                    DB::raw('audits.id as sequence'),
                    DB::raw('created_at as occured_at'),
                    DB::raw("concat('Payment method ',event) as description"),
                    DB::raw('ip_address'),
                    DB::raw('user_agent'),
                    DB::raw('new_values as metadata'),
                    DB::raw('url as url_reference'),
                    DB::raw("(case when user.id then concat(user.firstname,' ',user.lastname) else null end) as user_reference"),
                ])->leftJoin('user', function ($join) {
                    $join->on('user.id', '=', 'audits.user_id')
                        ->where('audits.user_type', '=', User::class);
                })->where('auditable_type', PaymentMethod::class)
                ->whereIn('auditable_id', $this->paymentMethods->pluck('id')));
        }

        // if rpps
        if ($this->recurringPaymentProfiles->count() > 0) {
            $history->union(DB::table('audits')
                ->select([
                    DB::raw('audits.id as sequence'),
                    DB::raw('created_at as occured_at'),
                    DB::raw("concat('Recurring payment ',event) as description"),
                    DB::raw('ip_address'),
                    DB::raw('user_agent'),
                    DB::raw('new_values as metadata'),
                    DB::raw('url as url_reference'),
                    DB::raw("(case when user.id then concat(user.firstname,' ',user.lastname) else null end) as user_reference"),
                ])->leftJoin('user', function ($join) {
                    $join->on('user.id', '=', 'audits.user_id')
                        ->where('audits.user_type', '=', User::class);
                })->where('auditable_type', RecurringPaymentProfile::class)
                ->whereIn('auditable_id', $this->recurringPaymentProfiles->pluck('id')));
        }

        return $history
            ->orderBy('occured_at', 'desc')
            ->orderBy('sequence', 'desc')
            ->get();
    }

    // ==============================
    // ==============================
    // ==============================
    // LEGACY FUNCTIONS FOR OLD VOLT TEMPLATES
    // ==============================
    // ==============================
    // ==============================

    /**
     * Attribute mask: membership
     *
     * @return \Ds\Models\Membership|null
     */
    public function getMembershipAttribute()
    {
        return $this->groups->sortByDesc(function ($group) {
            return [$group->pivot->is_active, $group->pivot->start_date];
        })->first();
    }

    public function getMembershipTimespanAttribute()
    {
        return $this->groupAccountTimespans->sortByDesc(function ($group) {
            return [$group->pivot->is_active, $group->pivot->start_date];
        })->first();
    }

    /**
     * Attribute mask: membership_expires_on
     *
     * @return \Ds\Domain\Shared\Date|null
     */
    public function getMembershipExpiresOnAttribute()
    {
        if (isset($this->membershipTimespan->pivot->end_date)) {
            return $this->asDate($this->membershipTimespan->pivot->end_date);
        }
    }

    /**
     * Attribute mask: is_membership_expired
     *
     * @return bool
     */
    public function getIsMembershipExpiredAttribute()
    {
        return $this->membership_expires_on && Carbon::parse($this->membership_expires_on)->endOfDay()->lt(fromLocal('now'));
    }

    /**
     * Attribute mask: average_order_amount
     *
     * @return string
     */
    public function getAverageOrderAmountAttribute()
    {
        return $this->orders()->avg(DB::raw('((totalamount - ifnull(refunded_amt,0)) * functional_exchange_rate)'));
    }

    /**
     * Attribute mask: total_order_amount
     *
     * @return string
     */
    public function getTotalOrderAmountAttribute()
    {
        return $this->payments()->succeededOrPending()->sum(DB::raw('((amount - amount_refunded) * functional_exchange_rate)'));
    }

    /**
     * Mutator: referral_code
     *
     * @return string
     */
    public function getReferralCodeAttribute()
    {
        return app('hashids')->encode($this->id);
    }

    /**
     * Returns a given url with the shareable referral code attached
     *
     * @return string
     */
    public function getShareableLink($url)
    {
        if ($url) {
            $url = Url::fromString($url)
                ->withQueryParameter('gcr', $this->referral_code);

            return secure_site_url((string) $url);
        }

        return '';
    }

    /**
     * Attaches referrer to model if it exists
     *
     * @return void
     */
    public function attachReferrer()
    {
        if (request()->cookie('gcr')) {
            $referral_member_id = request()->cookie('gcr');
            if (Member::where('id', $referral_member_id)->exists()) {
                $this->referred_by = $referral_member_id;
            }
        }
    }

    /**
     * Calculates how much that donor has given over their lifetime
     *
     * @return array
     */
    public function calculateGivingTotals($opts = [])
    {
        $opts = array_merge([
            'start_date' => null,
            'end_date' => null,
        ], $opts);

        $start_date = fromLocal($opts['start_date']);
        if ($start_date) {
            $start_date = $start_date->startOfDay()->toUtc();
        }

        $end_date = fromLocal($opts['end_date']);
        if ($end_date) {
            $end_date = $end_date->endOfDay()->toUtc();
        }

        // KNOWN ISSUE: this doesn't track refunds 100% corrently
        $orders = DB::table('productorderitem AS poi')
            ->join('productorder AS po', 'po.id', 'poi.productorderid')
            ->leftJoin('productinventory AS pi', 'pi.id', 'poi.productinventoryid')
            ->leftJoin('sponsorship AS sp', 'sp.id', 'poi.sponsorship_id')
            ->select([
                'po.member_id',
                DB::raw('SUM(CASE WHEN poi.sponsorship_id IS NOT NULL OR pi.is_donation = 1 THEN (poi.price * poi.qty) * po.functional_exchange_rate ELSE 0 END) as donation_total'),
                DB::raw('COUNT(DISTINCT (CASE WHEN poi.sponsorship_id IS NOT NULL OR pi.is_donation = 1 THEN po.id ELSE NULL END)) as donation_count'),
                DB::raw('SUM(CASE WHEN poi.sponsorship_id IS NULL AND pi.is_donation = 0 THEN (poi.price * poi.qty) * po.functional_exchange_rate ELSE 0 END) as purchase_total'),
                DB::raw('COUNT(DISTINCT (CASE WHEN poi.sponsorship_id IS NULL AND pi.is_donation = 0 THEN po.id ELSE NULL END)) as purchase_count'),
            ])->where('po.member_id', $this->id)
            ->whereNotNull('po.confirmationdatetime')
            ->whereNull('po.refunded_at');

        // Recurring Payments
        $rpps = DB::table('transactions AS t')
            ->join('recurring_payment_profiles AS rpp', 'rpp.id', 't.recurring_payment_profile_id')
            ->join('productorder AS po', 'po.id', 'rpp.productorder_id')
            ->leftJoin('productinventory AS pi', 'pi.id', 'rpp.productinventory_id')
            ->leftJoin('sponsorship AS sp', 'sp.id', 'rpp.sponsorship_id')
            ->select([
                'rpp.member_id',
                DB::raw('SUM(CASE WHEN rpp.sponsorship_id IS NOT NULL OR pi.is_donation = 1 THEN (t.amt - t.tax_amt - t.shipping_amt - t.dcc_amount) * t.functional_exchange_rate ELSE 0 END) as donation_total'),
                DB::raw('COUNT(DISTINCT (CASE WHEN rpp.sponsorship_id IS NOT NULL OR pi.is_donation = 1 THEN t.id ELSE NULL END)) as donation_count'),
                DB::raw('SUM(CASE WHEN rpp.sponsorship_id IS NULL AND pi.is_donation = 0 THEN (t.amt - t.tax_amt - t.shipping_amt - t.dcc_amount) * t.functional_exchange_rate ELSE 0 END) as purchase_total'),
                DB::raw('COUNT(DISTINCT (CASE WHEN rpp.sponsorship_id IS NULL AND pi.is_donation = 0 THEN t.id ELSE NULL END)) as purchase_count'),
            ])->where('rpp.member_id', $this->id)
            ->where('t.payment_status', 'Completed')
            ->whereNull('t.refunded_at');

        // Fundraisers
        $fundraisers = DB::table('productorderitem AS poi')
            ->join('productorder AS po', 'po.id', 'poi.productorderid')
            ->join('productinventory AS pi', 'pi.id', 'poi.productinventoryid')
            ->select([
                'poi.fundraising_member_id as member_id',
                DB::raw('SUM((poi.price * poi.qty) * po.functional_exchange_rate) as fundraising_total'),
                DB::raw('COUNT(DISTINCT po.id) as fundraising_count'),
            ])->where('poi.fundraising_member_id', $this->id)
            ->where('po.member_id', '!=', $this->id) // Don't include donations given to their own fundraiser
            ->whereNotNull('po.confirmationdatetime')
            ->whereNull('po.refunded_at');

        // Fundraiser Offsets
        $fundraisers_offsets = DB::table('fundraising_pages')
            ->select([
                DB::raw('MAX(currency_code) as currency_code'),
                DB::raw('SUM(amount_raised_offset) as amount_raised_offset'),
                DB::raw('SUM(donation_count_offset) as donation_count_offset'),
            ])->where('member_organizer_id', $this->id)
            ->groupBy('currency_code');

        if (! data_get(\Ds\Domain\Commerce\Models\PaymentProvider::getCreditCardProvider(false), 'test_mode')) {
            $orders->where('po.is_test', 0);
            $rpps->where('po.is_test', 0);
            $fundraisers->where('po.is_test', 0);
        }

        if ($start_date) {
            $orders->where('po.ordered_at', '>=', $start_date);
            $rpps->where('t.order_time', '>=', $start_date);
            $fundraisers->where('po.ordered_at', '>=', $start_date);
            $fundraisers_offsets->where('created_at', '>=', $start_date);
        }

        if ($end_date) {
            $orders->where('po.ordered_at', '<=', $end_date);
            $rpps->where('t.order_time', '<=', $end_date);
            $fundraisers->where('po.ordered_at', '<=', $end_date);
            $fundraisers_offsets->where('created_at', '<=', $end_date);
        }

        $orders = $orders->first();
        $rpps = $rpps->first();
        $fundraisers = $fundraisers->first();

        $fundraising_offset_amount = 0;
        $fundraising_offset_donor_count = 0;

        $fundraisers_offsets->get()
            ->each(function ($offset) use (&$fundraising_offset_amount, &$fundraising_offset_donor_count) {
                $fundraising_offset_amount += money($offset->amount_raised_offset, $offset->currency_code)->toCurrency(sys_get('dpo_currency'))->getAmount();
                $fundraising_offset_donor_count += $offset->donation_count_offset;
            });

        return [
            'donation_amount' => ($orders ? $orders->donation_total : 0) + ($rpps ? $rpps->donation_total : 0),
            'donation_count' => ($orders ? $orders->donation_count : 0) + ($rpps ? $rpps->donation_count : 0),
            'purchase_amount' => ($orders ? $orders->purchase_total : 0) + ($rpps ? $rpps->purchase_total : 0),
            'purchase_count' => ($orders ? $orders->purchase_count : 0) + ($rpps ? $rpps->purchase_count : 0),
            'fundraising_amount' => ($fundraisers ? $fundraisers->fundraising_total : 0) + $fundraising_offset_amount,
            'fundraising_count' => ($fundraisers ? $fundraisers->fundraising_count : 0) + $fundraising_offset_donor_count,
        ];
    }

    /**
     * Calculates how much that donor has given over their lifetime
     *
     * @return void
     */
    public function saveLifeTimeTotals()
    {
        $totals = $this->calculateGivingTotals();

        $this->lifetime_donation_amount = $totals['donation_amount'];
        $this->lifetime_donation_count = $totals['donation_count'];
        $this->lifetime_purchase_amount = $totals['purchase_amount'];
        $this->lifetime_purchase_count = $totals['purchase_count'];
        $this->lifetime_fundraising_amount = $totals['fundraising_amount'];
        $this->lifetime_fundraising_count = $totals['fundraising_count'];
        $this->save();
    }
}
