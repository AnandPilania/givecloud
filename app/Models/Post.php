<?php

namespace Ds\Models;

use Ds\Domain\Theming\Liquid\Liquidable;
use Ds\Eloquent\Hashids;
use Ds\Eloquent\HasMetadata;
use Ds\Eloquent\Metadatable;
use Ds\Eloquent\Permissions;
use Ds\Eloquent\Userstamps;
use Ds\Illuminate\Database\Eloquent\Factories\HasFactory;
use Ds\Illuminate\Database\Eloquent\Model;
use Ds\Models\Observers\PostObserver;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Str;

class Post extends Model implements Liquidable, Metadatable
{
    use HasFactory;
    use Hashids;
    use HasMetadata;
    use Permissions;
    use Userstamps;

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = 'modifieddatetime';

    /**
     * The name of the "updated by" column.
     *
     * @var string
     */
    const UPDATED_BY = 'modifiedbyuserid';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'post';

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
    protected $dates = [
        'modifieddatetime',
        'postdatetime',
        'expirydatetime',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'isenabled' => 'integer',
        'sequence' => 'integer',
        'expired_reasons' => 'array',
        'is_expired' => 'boolean',
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        self::observe(new PostObserver);
    }

    public function categories()
    {
        return $this->morphToMany(Category::class, 'categorizable');
    }

    public function postType()
    {
        return $this->belongsTo(PostType::class, 'type');
    }

    public function enclosure(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'media_id');
    }

    public function featuredImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'featured_image_id');
    }

    public function altImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'alt_image_id');
    }

    public function media(): MorphToMany
    {
        return $this->morphToMany(Media::class, 'mediable');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'modifiedbyuserid');
    }

    /**
     * Attribute Mutator: Url Slug
     *
     * @param string $value
     */
    public function setUrlSlugAttribute($value)
    {
        if ($this->url_slug === $value) {
            return;
        }

        $this->attributes['url_slug'] = self::getUniqueName($value);
    }

    /**
     * Scope: Active Posts
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeActive($query)
    {
        $query->where('isenabled', 1);

        // publish date (start date)
        $query->where(function ($query) {
            $query->whereNull('postdatetime');
            $query->orWhere('postdatetime', '<=', now());
        });

        // expiry date (end date)
        $query->where(function ($query) {
            $query->whereNull('expirydatetime');
            $query->orWhere('expirydatetime', '>=', now());
        });
    }

    /**
     * Attribute Mask: this_url
     *
     * @return string
     */
    public function getThisUrlAttribute()
    {
        return "/{$this->postType->url_slug}/{$this->url_slug}";
    }

    /**
     * Attribute Mask: Absolute URL
     *
     * @return string
     */
    public function getAbsoluteUrlAttribute()
    {
        return secure_site_url($this->this_url);
    }

    /**
     * Mutator: share_url
     * The URL to the post page for sharing links
     *
     * @return string
     */
    public function getShareUrlAttribute()
    {
        $member = member();

        return $member ? $member->getShareableLink($this->absolute_url) : $this->absolute_url;
    }

    public function getExpiredReasonsAttribute($reasons)
    {
        if (isset($reasons)) {
            return $reasons;
        }

        // empty array
        $reasons = [];

        // too late
        if (isset($this->expirydatetime) && $this->expirydatetime !== '' && $this->expirydatetime !== '0000-00-00 00:00:00') {
            if (toLocal($this->expirydatetime)->lt(fromLocal('today'))) {
                $reasons[] = 'too late';
            }
        }

        // too early
        if (isset($this->postdatetime) && $this->postdatetime !== '' && $this->postdatetime !== '0000-00-00 00:00:00') {
            if (toLocal($this->postdatetime)->gt(fromLocal('today'))) {
                $reasons[] = 'too early';
            }
        }

        // offline
        if ($this->isenabled == 0) {
            $reasons[] = 'offline';
        }

        // return reasons
        return $reasons;
    }

    public function getIsExpiredAttribute($is_expired)
    {
        return count($this->expired_reasons) > 0;
    }

    /**
     * Get a unique name for a given post.
     *
     * @param string $name
     * @return string
     */
    public static function getUniqueName($name)
    {
        $name = Str::slug(sanitize_filename($name));

        $existing = static::query()
            ->where('url_slug', $name)
            ->orWhere(function ($query) use ($name) {
                $query->where('url_slug', 'rlike', '^' . preg_quote($name) . '-[0-9]+$');
            })->orderByRaw('LENGTH(url_slug) DESC')
            ->orderBy('url_slug', 'desc')
            ->value('url_slug');

        if ($existing) {
            $sequence = preg_replace('/^' . preg_quote($name) . '-([0-9]+)$/', '$1', $existing);
            $name = sprintf("$name-%d", is_numeric($sequence) ? $sequence + 1 : 1);
        }

        return $name;
    }

    public static function getTemplateSuffixes()
    {
        return app('theme')->getAssetList('templates/post.*.liquid')
            ->map(function ($key) {
                return preg_replace('#templates/post\.(.*)\.liquid#', '$1', $key);
            })->toArray();
    }

    /**
     * Liquid representation of model.
     */
    public function toLiquid()
    {
        return \Ds\Domain\Theming\Liquid\Drop::factory($this, 'Post');
    }
}
