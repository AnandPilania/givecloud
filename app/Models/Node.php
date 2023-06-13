<?php

namespace Ds\Models;

use Ds\Domain\Theming\Liquid\Liquidable;
use Ds\Eloquent\HasMetadata;
use Ds\Eloquent\Metadatable;
use Ds\Eloquent\Permissions;
use Ds\Eloquent\Userstamps;
use Ds\Enums\NodeType;
use Ds\Illuminate\Database\Eloquent\Factories\HasFactory;
use Ds\Illuminate\Database\Eloquent\Model;
use Ds\Services\NodeRevisionsService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Node extends Model implements Liquidable, Metadatable
{
    use HasFactory;
    use HasMetadata;
    use Permissions;
    use Userstamps;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'node';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'sequence' => 'integer',
        'level' => 'integer',
        'isactive' => 'boolean',
        'ishidden' => 'boolean',
        'protected' => 'boolean',
        'autosave' => 'boolean',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Node::class, 'parentid');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(Node::class, 'parentid')
            ->where('type', NodeType::REVISION);
    }

    public function scopeWithoutAutosave(Builder $query): void
    {
        $query->where('autosave', false);
    }

    public function scopeWithoutRevisions(Builder $query): void
    {
        $query->where('type', '!=', NodeType::REVISION);
    }

    public function autosaveRevision(): HasOne
    {
        return $this->hasOne(Node::class, 'parentid')
            ->where('type', NodeType::REVISION)
            ->where('autosave', true);
    }

    /**
     * Children secured by membershiplevel
     */
    public function children(): HasMany
    {
        return $this->hasMany(Node::class, 'parentid')
            ->withoutRevisions()
            ->where('ishidden', 0)
            ->where('isactive', 1)
            ->orderBy('sequence', 'asc')
            ->securedByMembership()
            ->securedByLogin();
    }

    public function childrenAll(): HasMany
    {
        return $this->hasMany(Node::class, 'parentid')
            ->withoutRevisions()
            ->where('ishidden', 0)
            ->where('isactive', 1)
            ->orderBy('sequence', 'asc');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function featuredImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'featured_image_id');
    }

    public function altImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'alt_image_id');
    }

    public function hasMoreRecentAutosave(): bool
    {
        return (bool) optional($this->autosaveRevision->created_at)->gt($this->updated_at);
    }

    /**
     * The absolute URL to the node.
     *
     * @return string
     */
    public function absUrl($with_domain = false)
    {
        if ($with_domain) {
            return secure_site_url($this->abs_url);
        }

        return $this->abs_url;
    }

    /**
     * The absolute URL to the node.
     *
     * @return string|null
     */
    public function getAbsUrlAttribute()
    {
        switch ($this->type) {
            case 'category':
                return $this->category->url ?? null;
            case 'menu':
                return $this->url;
            case 'html':
            case 'liquid':
            case 'advanced':
                if ($this->code === 'home') {
                    return '/';
                }

                $path = '/' . trim($this->url, '/');

                if (Str::endsWith($path, '.php')) {
                    return substr($path, 0, strlen($path) - 4);
                }

                return $path;
        }
    }

    /**
     * Mutator: share_url
     * The URL to the node for sharing links
     *
     * @return string
     */
    public function getShareUrlAttribute()
    {
        $member = member();

        return $member ? $member->getShareableLink($this->abs_url) : $this->abs_url;
    }

    /**
     * Scope: Active nodes.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param int $parentId
     */
    public function scopeActive($query, $parentId = 0)
    {
        $query->where('isactive', 1);
        $query->where('parentid', $parentId);
        $query->orderBy('sequence', 'asc');

        // if you are NOT asking for the pages belonging to a parent page
        // we want to filter the top level by ishidden = 0
        if ($parentId === 0) {
            $query->where('ishidden', 0);
        }
    }

    /**
     * Scope: Menus.
     *
     * @param \Illuminate\Database\Query\Builder $query
     */
    public function scopeMenus($query)
    {
        $query->where('node.type', 'menu');
        $query->where(function ($query) {
            $query->whereNull('node.parentid');
            $query->orWhere('node.parentid', 0);
        });
    }

    /**
     * Scope: Membership access.
     *
     * @param \Illuminate\Database\Query\Builder $query
     */
    public function scopeSecuredByMembership($query)
    {
        // make sure we only return content columns
        $query->select('node.*')->distinct();

        // link up with categories
        $query->join('productcategory', 'productcategory.id', '=', 'node.category_id', 'left');

        // membership links based on categories
        $query->join('membership_access AS m1', function ($join) {
            $join->on('m1.parent_type', '=', DB::raw("'product_category'"))
                ->where('m1.parent_id', '=', DB::raw('node.category_id'));
        }, null, null, 'left');

        // membership links based on content
        $query->join('membership_access AS m2', function ($join) {
            $join->on('m2.parent_type', '=', DB::raw("'node'"))
                ->where('m2.parent_id', '=', DB::raw('node.id'));
        }, null, null, 'left');

        // apply filter
        $query->where(function ($qry) {
            // public content
            $qry->where(function ($qry) {
                $qry->whereNull('m1.id')->whereNull('m2.id');
            });

            // private content
            if (member()) {
                $activeGroups = member()->activeGroups();

                if ($activeGroups->count() > 0) {
                    $qry->orWhere(function ($q) use ($activeGroups) {
                        $q->whereNotNull('m1.id')
                            ->whereIn('m1.membership_id', $activeGroups->pluck('id')->all());
                    });

                    $qry->orWhere(function ($q) use ($activeGroups) {
                        $q->whereNotNull('m2.id')
                            ->whereIn('m2.membership_id', $activeGroups->pluck('id')->all());
                    });
                }
            }
        });
    }

    /**
     * Scope: Login access.
     *
     * @param \Illuminate\Database\Query\Builder $query
     */
    public function scopeSecuredByLogin($query)
    {
        if (! member_is_logged_in()) {
            $query->where(function ($query) {
                $query->where('requires_login', 0);
                $query->orWhere('hide_menu_link_when_logged_out', 0);
            });
        }
    }

    /**
     * Suggest a server file URL based on the title of this page and how it's been placed in the menu.
     *
     * @param string $title
     * @return string
     */
    public function suggestServerFile($title = null)
    {
        $path = trim($title ?? $this->title);
        $path = trim($path, '/');
        $path = strtolower($path);

        $path = collect(explode('/', $path))
            ->map(function ($segment) {
                return sanitize_filename($segment);
            })->reject(function ($segment) {
                return empty($segment);
            });

        if ($this->parent && ! ($this->parent->type === 'menu' && ! $this->parent->parentid)) {
            $path->prepend($this->parent->suggestServerFile());
        }

        return $path->implode('/');
    }

    /**
     * Attribute Mutator: Url
     *
     * @param string $value
     */
    public function setUrlAttribute($value)
    {
        if ($this->type === 'html') {
            $path = trim($value);
            $path = trim($path, '/');
            $path = strtolower($path);

            if (Str::endsWith($path, '.php')) {
                $path = substr($path, 0, strlen($path) - 4);
            }

            $path = collect(explode('/', $path))
                ->reject(function ($segment) {
                    return empty($segment);
                })->map(function ($segment) {
                    return Str::slug($segment);
                });

            $value = $path->implode('/');
        }

        $this->attributes['url'] = $value;
    }

    public static function getTemplateSuffixes()
    {
        return reqcache('template-suffixes:node#theming', function () {
            return app('theme')->getAssetList('templates/page.*.liquid')
                ->map(function ($key) {
                    return preg_replace('#templates/page\.(.*)\.liquid#', '$1', $key);
                })->toArray();
        });
    }

    /**
     * Get the top-level menu item that this node belongs to
     *
     * @return Node|null
     */
    public function getTopLevelParent()
    {
        $node = $this;

        do {
            // it shouldn't happen that a node is it's own parent
            // however there are some sites that have pages where this
            // is the case. adding this escape hatch in the meantime.
            if ($node->id == $node->parentid) {
                return null;
            }

            /** @var \Ds\Models\Node */
            $node = $node->parent;
        } while (isset($node->parent));

        return $node;
    }

    /**
     * Get whether or not this node belongs to the Donor Portal Menu
     *
     * @return bool
     */
    public function isChildOfDonorPortalMenu()
    {
        $parent = $this->getTopLevelParent();

        return $parent && $parent->title == 'Donor Portal Menu';
    }

    public function supportsRevisions(): bool
    {
        return $this->getRevisionService()->supportsRevisions();
    }

    public function requestHasChangesForRevisableContent(?Request $request = null): bool
    {
        return $this->getRevisionService()->requestHasChangesForRevisableContent($request);
    }

    public function createRevision(bool $autosave = false): void
    {
        $this->getRevisionService()->createRevision($autosave);
    }

    public function useRevision(int $revisionId): void
    {
        $this->getRevisionService()->useRevision($revisionId);
    }

    protected function getRevisionService(): NodeRevisionsService
    {
        return app(NodeRevisionsService::class, ['node' => $this]);
    }

    /**
     * Liquid representation of model.
     */
    public function toLiquid()
    {
        if ($this->type === 'menu') {
            return \Ds\Domain\Theming\Liquid\Drop::factory($this, 'Link');
        }

        return \Ds\Domain\Theming\Liquid\Drop::factory($this, 'Page');
    }
}
