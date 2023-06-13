<?php

namespace Ds\Models;

use Ds\Domain\Theming\Liquid\Liquidable;
use Ds\Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItemFile extends Model implements Liquidable
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'productorderitemfiles';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['granted'];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'expiration' => 'integer',
        'accessed' => 'integer',
        'download_limit' => 'integer',
        'address_limit' => 'integer',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'url',
        'type',
        'expired',
        'expiration_time',
        'days_left',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class, 'orderitemid');
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class, 'fileid');
    }

    /**
     * Mutator: url
     *
     * @return string|null
     */
    public function getUrlAttribute()
    {
        if ($this->type == 'external') {
            return null;
        }

        return secure_site_url('/ds/file?o=' . app('hashids')->encode($this->id));
    }

    /**
     * Mutator: type
     *
     * @return string
     */
    public function getTypeAttribute()
    {
        return (! empty($this->external_resource_uri)) ? 'external' : 'file';
    }

    /**
     * Mutator: expired
     *
     * @return bool
     */
    public function getExpiredAttribute()
    {
        if ($this->expiration == -1) {
            return false;
        }

        return now()->greaterThan($this->expiration_time);
    }

    /**
     * Mutator: expiration time
     *
     * @return \Ds\Domain\Shared\DateTime|null
     */
    public function getExpirationTimeAttribute()
    {
        if ($this->expiration == -1) {
            return null;
        }

        return toLocal($this->expiration);
    }

    /**
     * Mutator: days_left
     *
     * @return int
     */
    public function getDaysLeftAttribute()
    {
        return ($this->expiration_time && $this->expiration_time->isFuture()) ? $this->expiration_time->diffInDays() : null;
    }

    /**
     * Liquid representation of model.
     */
    public function toLiquid()
    {
        return \Ds\Domain\Theming\Liquid\Drop::factory($this, 'PurchasedMedia');
    }
}
