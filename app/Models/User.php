<?php

namespace Ds\Models;

use Closure;
use Ds\Domain\Shared\Exceptions\PermissionException;
use Ds\Eloquent\HasMetadata;
use Ds\Eloquent\Metadatable;
use Ds\Eloquent\Permissions;
use Ds\Eloquent\SoftDeleteUserstamp;
use Ds\Eloquent\Userstamps;
use Ds\Illuminate\Auth\Autologinable as AutologinableContract;
use Ds\Illuminate\Auth\Concerns\Autologinable;
use Ds\Illuminate\Database\Eloquent\Factories\HasFactory;
use Ds\Illuminate\Database\Eloquent\Model;
use Ds\Mail\ResetPassword as ResetPasswordMailable;
use Ds\Models\Observers\UserObserver;
use Ds\Models\Traits\HasSocialIdentities;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Passport\HasApiTokens;

class User extends Model implements
    AuthenticatableContract,
    AuthorizableContract,
    AutologinableContract,
    CanResetPasswordContract,
    Metadatable
{
    use Authenticatable;
    use Authorizable;
    use Autologinable;
    use CanResetPassword;
    use HasApiTokens;
    use HasFactory;
    use HasMetadata;
    use HasSocialIdentities;
    use Notifiable;
    use Permissions;
    use SoftDeletes;
    use SoftDeleteUserstamp;
    use TwoFactorAuthenticatable;
    use Userstamps;

    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    const CREATED_AT = 'createddatetime';

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = 'modifieddatetime';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'user';

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'hashed_password',
        'api_token',
        'credential',
        'permissions_json',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'last_login_at',
        'billing_warning_suppression_expiry_date',
        'last_opened_updates_feed_at',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'isadminuser' => 'boolean',
        'permissions_json' => 'array',
        'is_account_admin' => 'boolean',
        'ds_corporate_optin' => 'boolean',
        'notify_digest_daily' => 'boolean',
        'notify_digest_weekly' => 'boolean',
        'notify_digest_monthly' => 'boolean',
        'notify_recurring_batch_summary' => 'boolean',
        'notify_fundraising_page_activated' => 'boolean',
        'notify_fundraising_page_edited' => 'boolean',
        'notify_fundraising_page_closed' => 'boolean',
        'notify_fundraising_page_abuse' => 'boolean',
    ];

    public function logins(): HasMany
    {
        return $this->hasMany(UserLogin::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(Member::class, 'created_by');
    }

    public function resthookSubscriptions(): HasMany
    {
        return $this->hasMany(ResthookSubscription::class);
    }

    /**
     * Scope: Not Super User
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeNotSuperUser($query)
    {
        $query->whereNotIn('id', [config('givecloud.super_user_id')]);
    }

    /**
     * Scope: active (active users)
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeActive($query)
    {
        $query->where('isadminuser', '=', 1)
            ->notSuperUser()
            ->orderBy('lastname', 'asc')
            ->orderBy('firstname', 'asc');
    }

    /**
     * Attribute Mutator: Name (required for Mailables)
     *
     * @return string
     */
    public function getNameAttribute()
    {
        return $this->full_name;
    }

    /**
     * Attribute Mutator: Full Name.
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        return $this->firstname . ' ' . $this->lastname;
    }

    /**
     * Attribute Mutator: Can Live Chat.
     *
     * @return bool
     */
    public function getCanLiveChatAttribute()
    {
        if (is_super_user() || isDev()) {
            return false;
        }

        if (sys_get('enable_intercom') == 'all') {
            return true;
        }

        if (sys_get('enable_intercom') == 'owners' && $this->is_account_admin) {
            return true;
        }

        return false;
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->hashed_password;
    }

    /**
     * Get the column name for the "remember me" token.
     *
     * @return string
     */
    public function getRememberTokenName()
    {
        return 'credential';
    }

    /**
     * Get the two factor authentication QR code URL.
     *
     * @return string
     */
    public function twoFactorQrCodeUrl()
    {
        return app(TwoFactorAuthenticationProvider::class)->qrCodeUrl(
            sys_get('clientShortName') . ' (Givecloud)',
            $this->email,
            decrypt($this->two_factor_secret)
        );
    }

    public function getIsAccountAdminAttribute(): bool
    {
        if (isGivecloudExpress()) {
            return true;
        }

        return (bool) $this->attributes['is_account_admin'] ?? false;
    }

    /**
     * Ensure the user's password has recently been confirmed.
     *
     * @param int $maximumSecondsSinceConfirmation
     * @return $this
     */
    public function ensurePasswordIsConfirmed(int $maximumSecondsSinceConfirmation = null): self
    {
        $maximumSecondsSinceConfirmation = $maximumSecondsSinceConfirmation ?: config('auth.password_timeout', 900);

        if ((time() - session('auth.password_confirmed_at', 0)) < $maximumSecondsSinceConfirmation) {
            abort(403);
        }

        return $this;
    }

    /**
     * Send the password reset notification.
     *
     * @param string $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        Mail::to($this)->send(new ResetPasswordMailable($this, $token));
    }

    public function getTestmodeToken(): string
    {
        if (! session()->has('testmode_token')) {
            session(['testmode_token' => (string) Str::uuid()]);
        }

        return session('testmode_token');
    }

    /**
     * Checks permissions and redirects on failure.
     *
     * @param string|array $permissions
     * @return bool
     */
    public function canOrRedirect($permissions, $url = '/jpanel', $all_must_be_true = false)
    {
        // if they don't have permissions
        if (! $this->can($permissions, $all_must_be_true)) {
            throw new PermissionException($permissions, $url);
        }

        // otherwise, return TRUE
        return true;
    }

    /**
     * Returns whether or not the user can do an action based on an array of permissions
     *
     * @return bool
     */
    public function can($permissions, $all_must_be_true = false)
    {
        $permissions = Arr::wrap($permissions);

        // temporary measure to prevent affinity connection sites from
        // accessing or making changes to billing
        if (in_array('admin.billing', $permissions) && site()->partner->identifier === 'ac') {
            return false;
        }

        if (isGivecloudExpress() && ! $this->permissionsAvailableInGivecloudExpress($permissions, (bool) $all_must_be_true)) {
            return false;
        }

        // if super user
        if (user('id') == $this->id && is_super_user()) {
            // always return true
            return true;
        }

        // if the current user is a account admin, always return TRUE
        if ($this->is_account_admin) {
            return true;
        }

        $failed_count = 0;

        // loop over each permission and check
        foreach ($permissions as $permission) {
            // check the permission
            $can_do = $this->_checkPermission($permission);

            // if we only need ONE permission to be true, and this permission IS true, return TRUE!
            if ($can_do && ! $all_must_be_true) {
                return true;
            }

            // otherwise, continue counting tracking counts

            $failed_count += (int) ! $can_do;
        }

        // if there were no failures, return true
        return $failed_count === 0;
    }

    /**
     * Track a user login
     */
    public function trackLogin(bool $viaRemember = false)
    {
        // now
        $now = now();

        // add to the user login table
        $userLogin = \Ds\Models\UserLogin::create([
            'login_at' => $now,
            'ip' => request()->ip(),
            'user_agent' => request()->server('HTTP_USER_AGENT'),
            'user_id' => $this->id,
            'via_remember' => $viaRemember,
        ]);

        session(['user_login_id' => $userLogin->getKey()]);

        // update user stats
        $this->login_count = $this->logins()->count();
        $this->last_login_at = $now;
        $this->save();
    }

    protected static function boot()
    {
        parent::boot();

        self::observe(new UserObserver());
    }

    /**
     * Returns whether or not the user has permission to do something.
     *
     * @return bool
     */
    private function _checkPermission($permission)
    {
        // if $this->permissions_json is NOT an array, bail
        if (! is_array($this->permissions_json)) {
            return false;
        }

        // if there is only a model passed in
        if (count(explode('.', $permission)) === 1) {
            // loop throgh all permissinos and find any permission matching that model
            foreach ($this->permissions_json as $perm) {
                if (strpos($perm, $permission . '.') === 0) {
                    return true;
                }
            }

            // no model found, so return false;
            return false;
        }

        // break $permission into module and level
        [$model, $level] = explode('.', $permission);

        // if the look-up being doing is a custom permission (not view/add/edit); return the permission straight up
        if (! in_array($level, ['view', 'edit', 'add'])) {
            return in_array($permission, $this->permissions_json);
        }

        // check the user's permission array
        return
               ($level === 'edit' && in_array($model . '.edit', $this->permissions_json))
            || ($level === 'add' && (in_array($model . '.add', $this->permissions_json) || in_array($model . '.edit', $this->permissions_json)))
            || ($level === 'view' && (in_array($model . '.view', $this->permissions_json) || in_array($model . '.add', $this->permissions_json) || in_array($model . '.edit', $this->permissions_json)));
    }

    /**
     * Provide an email/username and this will return whether or not the email/usernmae is valid
     *
     * @return bool
     */
    public static function validateUsername($username, $existing_user_id = null)
    {
        // build a query of existing users
        $existing_users = self::where('email', $username)
            ->where('isadminuser', '=', 1)
            ->whereNull('deleted_at');

        // if the existing_user_id is not null, make sure we EXCLUDE the existing user from the query
        if (isset($existing_user_id)) {
            $existing_users->where('id', '!=', $existing_user_id);
        }

        // return boolean
        return $existing_users->count() === 0;
    }

    /**
     * Returns the default permissions available as well as human references
     *
     * @return array
     */
    public static function permissionStructure()
    {
        $permissions = [];

        $permissions[] = ['id' => 'node.view', 'name' => 'Can view menus & pages (read only)', 'category' => 'Website Content'];
        $permissions[] = ['id' => 'node.add', 'name' => 'Can add menus & pages (and edit their own pages)', 'category' => 'Website Content'];
        $permissions[] = ['id' => 'node.edit', 'name' => 'Can manage menus & pages (edit & delete)', 'category' => 'Website Content'];
        $permissions[] = ['id' => 'post.view', 'name' => 'Can view posts (read only)', 'category' => 'Website Content'];
        $permissions[] = ['id' => 'post.add', 'name' => 'Can add posts', 'category' => 'Website Content'];
        $permissions[] = ['id' => 'post.edit', 'name' => 'Can manage posts (edit & delete)', 'category' => 'Website Content'];
        $permissions[] = ['id' => 'posttype.edit', 'name' => 'Can manage feeds (edit & delete)', 'category' => 'Website Content'];

        $permissions[] = ['id' => 'customize.edit', 'name' => 'Can customize site', 'category' => 'Site Design'];
        $permissions[] = ['id' => 'template.edit', 'name' => 'Can manage theme & templates', 'category' => 'Site Design'];

        $permissions[] = ['id' => 'file.view', 'name' => 'Can view files (read only)', 'category' => 'Files & Storage'];
        $permissions[] = ['id' => 'file.edit', 'name' => 'Can manage files', 'category' => 'Files & Storage'];

        $permissions[] = ['id' => 'product.view', 'name' => 'Can view products & fundraising experiences (read only)', 'category' => 'Products'];
        $permissions[] = ['id' => 'product.add', 'name' => 'Can add products & fundraising experiences', 'category' => 'Products'];
        $permissions[] = ['id' => 'product.edit', 'name' => 'Can manage products & fundraising experiences (edit & delete)', 'category' => 'Products'];
        $permissions[] = ['id' => 'productcategory.edit', 'name' => 'Can manage product categories', 'category' => 'Products'];
        $permissions[] = ['id' => 'promocode.edit', 'name' => 'Can manage promo codes', 'category' => 'Products'];

        $permissions[] = ['id' => 'order.view', 'name' => 'Can view contributions', 'category' => 'Sell & Fundraise'];
        $permissions[] = ['id' => 'order.fullfill', 'name' => 'Can fulfill contributions (mark as complete & ship)', 'category' => 'Sell & Fundraise'];
        $permissions[] = ['id' => 'order.edit', 'name' => 'Can manage contributions (edit & delete)', 'category' => 'Sell & Fundraise'];
        $permissions[] = ['id' => 'order.refund', 'name' => 'Can refund contributions', 'category' => 'Sell & Fundraise'];

        if (feature('tax_receipt')) {
            $permissions[] = ['id' => 'taxreceipt.view', 'name' => 'Can view tax receipts (read only)', 'category' => 'Sell & Fundraise'];
            $permissions[] = ['id' => 'taxreceipt.edit', 'name' => 'Can issue, manage & revise receipts.', 'category' => 'Sell & Fundraise'];
        }

        $permissions[] = ['id' => 'tribute.view', 'name' => 'Can view tributes (read only)', 'category' => 'Sell & Fundraise'];
        $permissions[] = ['id' => 'tribute.edit', 'name' => 'Can manage tributes', 'category' => 'Sell & Fundraise'];
        $permissions[] = ['id' => 'fundraisingpages.edit', 'name' => 'Can manage peer-to-peer', 'category' => 'Sell & Fundraise'];
        $permissions[] = ['id' => 'pledgecampaigns.edit', 'name' => 'Can manage pledge campaigns', 'category' => 'Sell & Fundraise'];
        $permissions[] = ['id' => 'pledges.edit', 'name' => 'Can manage pledges', 'category' => 'Sell & Fundraise'];
        $permissions[] = ['id' => 'pos.edit', 'name' => 'Can manage point-of-sale', 'category' => 'Sell & Fundraise'];

        if (feature('messenger')) {
            $permissions[] = ['id' => 'messenger.edit', 'name' => 'Can manage messenger', 'category' => 'Sell & Fundraise'];
        }

        if (feature('sponsorship')) {
            $permissions[] = ['id' => 'sponsorship.view', 'name' => 'Can view sponsorship records (read only, export)', 'category' => 'Sponsorship'];
            $permissions[] = ['id' => 'sponsorship.add', 'name' => 'Can add sponsorship records', 'category' => 'Sponsorship'];
            $permissions[] = ['id' => 'sponsorship.edit', 'name' => 'Can manage sponsorship records', 'category' => 'Sponsorship'];
            $permissions[] = ['id' => 'segment.edit', 'name' => 'Can manage sponsorship custom fields', 'category' => 'Sponsorship'];
            $permissions[] = ['id' => 'paymentoption.edit', 'name' => 'Can manage sponsorship payment options', 'category' => 'Sponsorship'];

            $permissions[] = ['id' => 'sponsor.view', 'name' => 'Can view sponsors', 'category' => 'Sponsorship'];
            $permissions[] = ['id' => 'sponsor.add', 'name' => 'Can add sponsors', 'category' => 'Sponsorship'];
            $permissions[] = ['id' => 'sponsor.edit', 'name' => 'Can manage sponsors', 'category' => 'Sponsorship'];
            $permissions[] = ['id' => 'sponsor.mature', 'name' => 'Can view matured sponsorships', 'category' => 'Sponsorship'];
        }

        if (feature('accounts')) {
            $permissions[] = ['id' => 'member.view', 'name' => 'Can view supporters (read only)', 'category' => 'Supporters'];
            $permissions[] = ['id' => 'member.add', 'name' => 'Can add supporters', 'category' => 'Supporters'];
            $permissions[] = ['id' => 'member.edit', 'name' => 'Can manage supporters', 'category' => 'Supporters'];
            $permissions[] = ['id' => 'member.merge', 'name' => 'Can merge supporters', 'category' => 'Supporters'];
            $permissions[] = ['id' => 'member.login', 'name' => 'Can login as any supporter', 'category' => 'Supporters'];
            $permissions[] = ['id' => 'membership.edit', 'name' => 'Can manage membership levels', 'category' => 'Supporters'];
            $permissions[] = ['id' => 'account.edit', 'name' => 'Can manage supporter settings', 'category' => 'Supporters'];
        }

        if (feature('kiosks')) {
            $permissions[] = ['id' => 'kiosk.view', 'name' => 'Can view kiosks (read only)', 'category' => 'Kiosks'];
            $permissions[] = ['id' => 'kiosk.add', 'name' => 'Can add kiosks', 'category' => 'Kiosks'];
            $permissions[] = ['id' => 'kiosk.edit', 'name' => 'Can manage kiosks', 'category' => 'Kiosks'];
        }

        if (feature('virtual_events')) {
            $permissions[] = ['id' => 'virtualevents.view', 'name' => 'Can view virtual events (read only)', 'category' => 'Virtual Events'];
            $permissions[] = ['id' => 'virtualevents.add', 'name' => 'Can add virtual events', 'category' => 'Virtual Events'];
            $permissions[] = ['id' => 'virtualevents.edit', 'name' => 'Can manage virtual events', 'category' => 'Virtual Events'];
        }

        if (! sys_get('rpp_donorperfect')) {
            $permissions[] = ['id' => 'recurringpaymentprofile.view', 'name' => 'View recurring transactions', 'category' => 'Recurring Txns'];
            $permissions[] = ['id' => 'recurringpaymentprofile.edit', 'name' => 'Manage recurring transactions', 'category' => 'Recurring Txns'];
            $permissions[] = ['id' => 'recurringpaymentprofile.charge', 'name' => 'Manually charge a recurring transaction', 'category' => 'Recurring Txns'];
            $permissions[] = ['id' => 'transaction.view', 'name' => 'View transaction history', 'category' => 'Recurring Txns'];
            $permissions[] = ['id' => 'transaction.refund', 'name' => 'Manually refund a recurring transaction', 'category' => 'Recurring Txns'];
        }

        $permissions[] = ['id' => 'dashboard.view', 'name' => 'Can view dashboard', 'category' => 'Administration'];

        $permissions[] = ['id' => 'admin.accounts', 'name' => 'Can manage supporter settings', 'category' => 'Administration'];
        $permissions[] = ['id' => 'admin.billing', 'name' => 'Can manage billing settings', 'category' => 'Administration'];
        $permissions[] = ['id' => 'admin.general', 'name' => 'Can manage general organization settings', 'category' => 'Administration'];
        $permissions[] = ['id' => 'admin.website', 'name' => 'Can manage website settings', 'category' => 'Administration'];

        $permissions[] = ['id' => 'email.edit', 'name' => 'Can manage email notifications', 'category' => 'Administration'];
        $permissions[] = ['id' => 'tax.edit', 'name' => 'Can manage taxes', 'category' => 'Administration'];
        $permissions[] = ['id' => 'tributetype.edit', 'name' => 'Can manage tribute types', 'category' => 'Administration'];
        $permissions[] = ['id' => 'shipping.edit', 'name' => 'Can manage shipping', 'category' => 'Administration'];
        $permissions[] = ['id' => 'alias.edit', 'name' => 'Can manage redirects', 'category' => 'Administration'];
        $permissions[] = ['id' => 'user.edit', 'name' => 'Can manage users & permissions', 'category' => 'Administration'];
        $permissions[] = ['id' => 'admin.advanced', 'name' => 'Can manage advanced settings', 'category' => 'Administration'];
        $permissions[] = ['id' => 'admin.dpo', 'name' => 'Can manage DonorPerfect integration', 'category' => 'Administration'];
        $permissions[] = ['id' => 'hooks.edit', 'name' => 'Can manage Webhooks integration', 'category' => 'Administration'];
        $permissions[] = ['id' => 'userdefinedfields.edit', 'name' => 'Can manage User Defined Fields', 'category' => 'Administration'];

        $permissions[] = ['id' => 'reports.payments_details', 'name' => 'Payments Details', 'category' => 'Reports'];
        $permissions[] = ['id' => 'reports.product_orders', 'name' => 'Product Contributions', 'category' => 'Reports'];
        $permissions[] = ['id' => 'reports.orders_by_product', 'name' => 'Contributions by Product', 'category' => 'Reports'];
        $permissions[] = ['id' => 'reports.referral_sources',  'name' => 'Referral Sources',  'category' => 'Reports'];
        $permissions[] = ['id' => 'reports.orders_by_customer', 'name' => 'Contributions by Customer', 'category' => 'Reports'];
        $permissions[] = ['id' => 'reports.stock_levels', 'name' => 'Product Stock Levels', 'category' => 'Reports'];
        $permissions[] = ['id' => 'reports.donor_covers_costs', 'name' => 'Donor Covers Costs', 'category' => 'Reports'];
        $permissions[] = ['id' => 'reports.pledge-campaigns', 'name' => 'Pledges Campaigns', 'category' => 'Reports'];
        $permissions[] = ['id' => 'reports.shipping', 'name' => 'Shipping', 'category' => 'Reports'];
        $permissions[] = ['id' => 'reports.gift_reconciliation', 'name' => 'DP Gift Reconciliation', 'category' => 'Reports'];
        $permissions[] = ['id' => 'reports.donor_reconciliation', 'name' => 'DP Donor Reconciliation', 'category' => 'Reports'];
        $permissions[] = ['id' => 'reports.abandoned_carts', 'name' => 'Abandoned Carts', 'category' => 'Reports'];
        $permissions[] = ['id' => 'reports.tax_reconciliation', 'name' => 'Tax Reconciliation', 'category' => 'Reports'];
        $permissions[] = ['id' => 'reports.settlements', 'name' => 'Settlement Batches', 'category' => 'Reports'];
        $permissions[] = ['id' => 'reports.transaction_fees', 'name' => 'Platform Fees', 'category' => 'Reports'];
        $permissions[] = ['id' => 'reports.check_ins', 'name' => 'Event Check-Ins', 'category' => 'Reports'];

        return $permissions;
    }

    /**
     * Notify this GC user
     *
     * @param \Illuminate\Mail\Mailable $mailable
     */
    public function mail(Mailable $mailable)
    {
        if ($this->email) {
            Mail::to($this)->send($mailable);
        }
    }

    /**
     * Notify the GC user admins
     *
     * This function also prevents the same notice going
     * out to all admins in the same 60min time period.
     * (see the cache references)
     */
    public static function mailAccountAdmins(Mailable $mailable, Closure $shouldSend = null): void
    {
        if (Cache::has(get_class($mailable))) {
            return;
        }

        foreach (self::where('is_account_admin', 1)->notSuperUser()->get() as $user) {
            if ($shouldSend && ! $shouldSend($user)) {
                continue;
            }

            $user->mail(clone $mailable);
        }

        Cache::put(get_class($mailable), true, fromUtc('+30 min'));
    }
}
