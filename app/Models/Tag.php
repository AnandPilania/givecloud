<?php

namespace Ds\Models;

use Ds\Eloquent\Userstamps;
use Ds\Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Tag extends Model
{
    use Userstamps;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = ['name'];

    public function media(): MorphToMany
    {
        return $this->morphedByMany(Media::class, 'taggable');
    }

    public function files(): MorphToMany
    {
        return $this->morphedByMany(File::class, 'taggable');
    }

    /**
     * Set the tag's name.
     *
     * @param string $value
     * @return void
     */
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = strtolower($value);
    }
}
