<?php

namespace Ds\Models;

use Ds\Domain\Theming\Liquid\Liquidable;
use Ds\Eloquent\Permissions;
use Ds\Eloquent\Userstamps;
use Ds\Illuminate\Database\Eloquent\Factories\HasFactory;
use Ds\Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountType extends Model implements Liquidable
{
    use HasFactory;
    use Permissions;
    use SoftDeletes;
    use Userstamps;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var string[]|bool
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_organization' => 'boolean',
        'on_web' => 'boolean',
        'is_default' => 'boolean',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        // always order account_types by sequence
        static::addGlobalScope('sequence', function (Builder $builder) {
            $builder->orderBy('sequence');
        });
    }

    /**
     * Scope: On Web
     *
     * @param \Illuminate\Database\Query\Builder $query
     */
    public function scopeOnWeb($query)
    {
        $member = member();

        $query->where(function ($qry) use ($member) {
            // make sure on_web flag is checked
            $qry->where('on_web', true);

            // if member is logged in, make sure we include
            // account type in the list of account types,
            // whether or not it is public
            if ($member && $member->account_type_id) {
                $qry->orWhere('id', $member->account_type_id);
            }
        });
    }

    /**
     * Scope: default
     *
     * @param \Illuminate\Database\Query\Builder $query
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', 1);
    }

    /**
     * Mutator: is_protected
     *
     * account_types.id = 1 are both protected (individual)
     */
    public function getIsProtectedAttribute()
    {
        return $this->id == 1 || $this->id == 2;
    }

    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the default account type
     * (there can only be one)
     *
     * @return self|null
     */
    public static function getDefault()
    {
        $default = self::where('is_default', 1)->get()->first();

        if ($default) {
            return $default;
        }

        // GRACEFUL FALLBACK
        // if no default was found,
        // set a default
        $individual = self::find(1);
        if ($individual) {
            $individual->is_default = 1;
            $individual->save();

            return $individual;
        }
    }

    /**
     * Liquid representation of model.
     */
    public function toLiquid()
    {
        return \Ds\Domain\Theming\Liquid\Drop::factory($this, 'AccountType');
    }
}
