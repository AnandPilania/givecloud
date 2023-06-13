<?php

namespace Ds\Models;

use Ds\Domain\Theming\Liquid\Drop;
use Ds\Domain\Theming\Liquid\Liquidable;
use Ds\Eloquent\Hashids;
use Ds\Eloquent\HasMetadata;
use Ds\Eloquent\Metadatable;
use Ds\Eloquent\Permissions;
use Ds\Eloquent\Userstamps;
use Ds\Illuminate\Database\Eloquent\Factories\HasFactory;
use Ds\Illuminate\Database\Eloquent\Model;
use Ds\Models\Traits\HasEmails;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Membership extends Model implements Liquidable, Metadatable
{
    use HasEmails;
    use HasFactory;
    use Hashids;
    use HasMetadata;
    use Permissions;
    use SoftDeletes;
    use Userstamps;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'membership';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'starts_at',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'days_to_expire' => 'integer',
        'show_in_profile' => 'boolean',
        'double_optin_required' => 'boolean',
        'members_can_manage_optin' => 'boolean',
        'members_can_manage_optout' => 'boolean',
        'members_can_view_directory' => 'boolean',
        'sequence' => 'integer',
    ];

    public function accounts(): BelongsToMany
    {
        return $this->belongsToMany(Account::class, 'group_account', 'group_id', 'account_id')
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

    public function promoCodes(): BelongsToMany
    {
        return $this->belongsToMany(PromoCode::class, 'membership_promocodes', 'membership_id', 'promocode', 'id', 'code');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(Variant::class);
    }

    /**
     * Scope: withMemberCount
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeWithMemberCount($query)
    {
        return $query->join(DB::raw('(SELECT group_id as membership_id, COUNT(DISTINCT account_id) AS member_count FROM group_account WHERE (start_date IS NULL OR start_date <= DATE(NOW())) AND (end_date IS NULL OR end_date >= DATE(NOW())) GROUP BY group_id) as t1'), 't1.membership_id', '=', 'membership.id', 'left');
    }

    /**
     * Scope: availableInProfile
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeAvailableInProfile($query)
    {
        return $query->where(function ($q) {
            $q->where('members_can_manage_optin', true)
                ->orWhere('members_can_manage_optout', true);
        });
    }

    /**
     * Mutator: ends_at
     *
     * @return \Carbon\Carbon|null
     */
    public function getEndsAtAttribute()
    {
        return ($this->starts_at && $this->days_to_expire) ? $this->starts_at->copy()->addDays($this->days_to_expire) : null;
    }

    /**
     * Mutator: expiry_description
     *
     * @return string|null
     */
    public function getExpiryDescriptionAttribute()
    {
        if ($this->starts_at && $this->ends_at) {
            return fromUtcFormat($this->starts_at, 'M j, Y') . ' to ' . fromUtcFormat($this->ends_at, 'M j, Y');
        }

        if ($this->starts_at) {
            return fromUtcFormat($this->starts_at, 'M j, Y') . ' to forever';
        }

        if ($this->days_to_expire) {
            return number_format($this->days_to_expire, 0) . ' days';
        }

        return null;
    }

    /**
     * Liquid representation of model.
     */
    public function toLiquid()
    {
        return Drop::factory($this, 'Group');
    }
}
