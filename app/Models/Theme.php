<?php

namespace Ds\Models;

use Ds\Domain\Theming\Liquid\Drop;
use Ds\Domain\Theming\Liquid\Liquidable;
use Ds\Illuminate\Database\Eloquent\Factories\HasFactory;
use Ds\Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Theme extends Model implements Liquidable
{
    use HasFactory;

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'locked' => 'boolean',
    ];

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class)
            ->whereNull('parent_id');
    }

    /**
     * Attribute Mutator: Active
     *
     * @param string $key
     */
    public function getActiveAttribute($key)
    {
        $activeTheme = (int) sys_get('active_theme');

        return $activeTheme === $this->id;
    }

    /**
     * Attribute Mutator: Thumbnail
     *
     * @param string $key
     */
    public function getThumbnailAttribute($key)
    {
        return "/static/{$this->handle}/assets/theme.png";
    }

    /**
     * Get the instance as Liquid representation of object.
     *
     * @return \Ds\Domain\Theming\Liquid\Drop|array
     */
    public function toLiquid()
    {
        return Drop::factory($this, 'Theme');
    }
}
