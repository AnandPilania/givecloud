<?php

namespace Ds\Models;

use Ds\Domain\Theming\Liquid\Liquidable;
use Ds\Eloquent\HasMetadata;
use Ds\Eloquent\Metadatable;
use Ds\Eloquent\Permissions;
use Ds\Eloquent\Userstamps;
use Ds\Illuminate\Database\Eloquent\Auditable;
use Ds\Illuminate\Database\Eloquent\Factories\HasFactory;
use Ds\Illuminate\Database\Eloquent\HasAuditing;
use Ds\Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductCategory extends Model implements Auditable, Liquidable, Metadatable
{
    use HasAuditing;
    use HasFactory;
    use HasMetadata;
    use Permissions;
    use Userstamps;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'productcategory';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var string[]|bool
     */
    protected $guarded = ['id'];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'ismale' => 'boolean',
        'isfemale' => 'boolean',
        'sequence' => 'integer',
        'parent_id' => 'integer',
    ];

    /**
     * An array to map relation names to their morph names in the database.
     *
     * @var array
     */
    public $relationMorphMap = [
        'memberships' => 'product_category',
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // we want to be sure the url is set and valid
            if (trim($model->url_name) == '') {
                if ($model->parent) {
                    $model->url_name = $model->parent->url_name . '/' . Str::slug($model->name);
                } else {
                    $model->url_name = Str::slug($model->name);
                }
            }
        });
    }

    public function nodes(): HasMany
    {
        return $this->hasMany(Node::class, 'category_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'parent_id');
    }

    public function childCategories(): HasMany
    {
        return $this->hasMany(ProductCategory::class, 'parent_id')
            ->orderBy('sequence');
    }

    public function memberships(): MorphToMany
    {
        return $this->morphToMany(Membership::class, 'parent', 'membership_access');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'productcategorylink', 'categoryid', 'productid');
    }

    public function promoCodes(): BelongsToMany
    {
        return $this->belongsToMany(PromoCode::class, 'productpromocodecategory', 'categoryid', 'promocodeid');
    }

    public function photo(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'media_id');
    }

    public function pledges(): MorphToMany
    {
        return $this->morphToMany(Pledge::class, 'pledgable');
    }

    /**
     * Scope: Top level categories.
     *
     * @param \Illuminate\Database\Query\Builder $query
     */
    public function scopeTopLevel($query)
    {
        $query->whereRaw('IFNULL(productcategory.parent_id,0) = 0');
    }

    /**
     * Scope: Top level categories.
     *
     * @param \Illuminate\Database\Query\Builder $query
     */
    public function scopeWithIsLocked($query)
    {
        $query->select($this->table . '.*')
            ->join(DB::raw("(select parent_id, COUNT(*) as lock_count from membership_access where parent_type = 'product_category' group by parent_id) as _x"), '_x.parent_id', '=', $this->table . '.id', 'left')
            ->addSelect(DB::raw('(CASE WHEN IFNULL(_x.lock_count,0) = 0 THEN 0 ELSE 1 END) as is_locked'));
    }

    /**
     * Scope: All categories that are not locked to .
     *
     * @param \Illuminate\Database\Query\Builder $query
     */
    public function scopeIsLocked($query)
    {
        return $query->select($this->table . '.*')->leftJoin('membership_access', 'membership_access.parent_id', '=', 'productcategory.id')->whereNull('membership_access.id');
    }

    /**
     * Scope: Limit results by parent id
     *
     * @param \Illuminate\Database\Query\Builder $query
     */
    public function scopeParentId($query, $parent_id)
    {
        $query->whereRaw('ifnull(`' . $this->table . '`.`parent_id`,0) = ?', [$parent_id]);
    }

    /**
     * The absolute URL to the category.
     *
     * @return string
     */
    public function getUrlAttribute()
    {
        return '/' . $this->url_name;
    }

    /**
     * Mutator: abs_url
     *
     * @return string
     */
    public function getAbsUrlAttribute()
    {
        return secure_site_url($this->url_name);
    }

    /**
     * Mutator: share_url
     * The URL to the product category for sharing links
     *
     * @return string
     */
    public function getShareUrlAttribute()
    {
        $member = member();

        return $member ? $member->getShareableLink($this->abs_url) : $this->abs_url;
    }

    /**
     * Get a unique list of product filters (product.author)
     * for a given category.
     *
     * REPLACES legacy product_get_author_list() FUNCTION
     *
     * @return \Illuminate\Support\Collection
     */
    public function getFiltersAttribute()
    {
        // filters list
        $filters = DB::table('product')
            ->join('productcategorylink', 'productcategorylink.productid', '=', 'product.id')
            ->select(DB::raw('trim(product.author) as name'))
            ->distinct()
            ->whereRaw(DB::raw("ifnull(trim(product.author),'') != ''"))
            ->where('productcategorylink.categoryid', '=', $this->id)
            ->whereNull('product.deleted_at')
            ->orderBy(DB::raw('trim(product.author)'))
            ->get();

        // add a formatted version of each filter
        foreach ($filters as $filter) {
            $filter->name_shortened = Str::limit($filter->name, 27);
        }

        // return the filters
        return $filters;
    }

    /**
     * Finds any linked nodes and updates them.
     * If no nodes found, it adds a node to the
     * end of the menu.
     *
     * @return void
     */
    public function updateNodes()
    {
        // if it's already linked to a node, update the
        // node's name to match the category's name
        if (count($this->nodes) > 0) {
            // update every linked node
            foreach ($this->nodes as $node) {
                $node->title = $this->name;
                $node->save();
            }

            // if there are no nodes, we need to create nodes
        } else {
            // but where do we add a node? on the main menu?
            // what if this category's PARENT category already
            // exists in the menu?  we should add it there
            // first... SO - lets find out
            $parent_category_nodes = ($this->parent_id != 0) ? Node::where('category_id', $this->parent_id)->get() : [];

            // if we found this categories parent category in
            // the existing menu nodes, lets add a node beneath
            // it for THIS category
            if (count($parent_category_nodes) > 0) {
                // for everytime this category's parent is in the menu
                foreach ($parent_category_nodes as $parent_node) {
                    // add a sub-node for this category
                    $node = new Node;
                    $node->parentid = $parent_node->id;
                    $node->sequence = $this->sequence;
                    $node->title = $this->name;
                    $node->type = 'category';
                    $node->isactive = true;
                    $node->ishidden = false;
                    $node->category_id = $this->id;
                    $node->save();
                }

                // if no parent category nodes were found, lets
            // create a new node at the root menu for this
            // category
            } else {
                // add a sub-node for this category
                $node = new Node;
                $node->parentid = 0;
                $node->sequence = $this->sequence;
                $node->title = $this->name;
                $node->type = 'category';
                $node->isactive = true;
                $node->ishidden = false;
                $node->category_id = $this->id;
                $node->save();
            }
        }
    }

    public static function getTemplateSuffixes()
    {
        return app('theme')->getAssetList('templates/collection.*.liquid')
            ->map(function ($key) {
                return preg_replace('#templates/collection\.(.*)\.liquid#', '$1', $key);
            })->toArray();
    }

    public function isSecuredByMembership(): bool
    {
        return $this->memberships()->exists();
    }

    /**
     * Scope: Membership access.
     *
     * @param \Illuminate\Database\Query\Builder $query
     */
    public function scopeSecuredByMembership($query)
    {
        // make sure we only return content columns
        $query->select('productcategory.*')->distinct();

        // membership links based on categories
        $query->join('membership_access AS m1', function ($join) {
            $join->on('m1.parent_type', '=', DB::raw("'product_category'"))
                ->where('m1.parent_id', '=', DB::raw('productcategory.id'));
        }, null, null, 'left');

        // apply filter
        $query->where(function ($qry) {
            // public content
            $qry->whereNull('m1.id');

            // private content
            if (member()) {
                $activeGroups = member()->activeGroups();

                if ($activeGroups->count() > 0) {
                    $qry->orWhere(function ($q) use ($activeGroups) {
                        $q->whereNotNull('m1.id')
                            ->whereIn('m1.membership_id', $activeGroups->pluck('id')->all());
                    });
                }
            }
        });
    }

    /**
     * Liquid representation of model.
     */
    public function toLiquid()
    {
        return \Ds\Domain\Theming\Liquid\Drop::factory($this, 'Category');
    }
}
