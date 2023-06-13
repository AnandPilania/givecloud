<?php

namespace Ds\Illuminate\Database\Eloquent\Relations;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany as IlluminateMorphToMany;

class MorphToMany extends IlluminateMorphToMany
{
    /**
     * Create a new morph to many relationship instance.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Illuminate\Database\Eloquent\Model $parent
     * @param string $name
     * @param string $table
     * @param string $foreignPivotKey
     * @param string $relatedPivotKey
     * @param string $parentKey
     * @param string $relatedKey
     * @param string|null $relationName
     * @param bool $inverse
     * @return void
     */
    public function __construct(Builder $query, Model $parent, $name, $table, $foreignPivotKey, $relatedPivotKey, $parentKey, $relatedKey, $relationName = null, $inverse = false)
    {
        $this->inverse = $inverse;
        $this->morphType = $name . '_type';

        $this->morphClass = $parent->relationMorphMap[$relationName]
            ?? ($inverse ? $query->getModel()->getMorphClass() : $parent->getMorphClass());

        BelongsToMany::__construct($query, $parent, $table, $foreignPivotKey, $relatedPivotKey, $parentKey, $relatedKey, $relationName);
    }
}
