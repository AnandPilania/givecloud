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
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PostType extends Model implements Liquidable, Metadatable
{
    use HasFactory;
    use Hashids;
    use HasMetadata;
    use Permissions;
    use Userstamps;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'posttype';

    public function allPosts(): HasMany
    {
        return $this->hasMany(Post::class, 'type');
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'type');
    }

    public function activePosts(): HasMany
    {
        return $this->posts()
            ->active();
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class, 'assignable_id')
            ->where('categories.assignable_type', '=', 'post_type');
    }

    public function photo(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'media_id');
    }

    /**
     * Attribute Mask: Absolute URL
     *
     * @return string
     */
    public function getAbsoluteUrlAttribute()
    {
        return secure_site_url($this->url_slug);
    }

    /**
     * Attribute Mask: Rss URL
     *
     * @return string
     */
    public function getRssLinkAttribute()
    {
        return secure_site_url('feed?i=' . $this->id);
    }

    /**
     * Mutator: share_url
     * The URL to the post type for sharing links
     *
     * @return string
     */
    public function getShareUrlAttribute()
    {
        $member = member();

        return $member ? $member->getShareableLink($this->absolute_url) : $this->absolute_url;
    }

    public static function getTemplateSuffixes()
    {
        return app('theme')->getAssetList('templates/post-type.*.liquid')
            ->map(function ($key) {
                return preg_replace('#templates/post-type\.(.*)\.liquid#', '$1', $key);
            })->toArray();
    }

    /**
     * Liquid representation of model.
     */
    public function toLiquid()
    {
        return \Ds\Domain\Theming\Liquid\Drop::factory($this, 'PostType');
    }
}
