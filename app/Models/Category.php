<?php

namespace Ds\Models;

use Ds\Domain\Theming\Liquid\Liquidable;
use Ds\Eloquent\Hashids;
use Ds\Eloquent\HasMetadata;
use Ds\Eloquent\Metadatable;
use Ds\Illuminate\Database\Eloquent\Factories\HasFactory;
use Ds\Illuminate\Database\Eloquent\Model;
use Ds\Models\Observers\CategoryObserver;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Str;

class Category extends Model implements Liquidable, Metadatable
{
    use Hashids;
    use HasFactory;
    use HasMetadata;

    public const SEPARATOR = '>';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'assignable_type',
        'assignable_id',
        'parent_id',
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        self::observe(new CategoryObserver);
    }

    public function posts(): MorphToMany
    {
        return $this->morphedByMany(Post::class, 'categorizable');
    }

    public function assignable(): MorphTo
    {
        return $this->morphTo();
    }

    public function photo(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'media_id');
    }

    public function parentCategory(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function childCategories(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')
            ->orderBy('sequence');
    }

    /**
     * Scope: Active categories
     *
     * @param \Illuminate\Database\Query\Builder $query
     */
    public function scopeActive($query)
    {
        return $query->where('enabled', 1);
    }

    /**
     * Liquid representation of model.
     */
    public function toLiquid()
    {
        return \Ds\Domain\Theming\Liquid\Drop::factory($this, 'PostTypeCategory');
    }

    /**
     * Create a unique handle.
     *
     * @param string $handle
     * @param int $iteration
     * @return string
     */
    public function createUniqueHandle($handle = null, $iteration = 0)
    {
        if (empty($handle)) {
            $handle = strtolower(Str::slug($this->name, '-') . (($iteration > 0) ? ('-' . $iteration) : ''));
        } else {
            $handle = strtolower($handle . (($iteration > 0) ? ('-' . $iteration) : ''));
        }

        if (self::where('handle', $handle)->where('id', '!=', $this->id)->count() == 0) {
            return $handle;
        }

        return $this->createUniqueHandle($handle, ++$iteration);
    }

    public function fullName(): string
    {
        $category = $this;
        while ($category) {
            $categoryPath[] = $category->name;
            $category = $category->parentCategory;
        }

        return implode(' ' . self::SEPARATOR . ' ', array_reverse($categoryPath));
    }
}
