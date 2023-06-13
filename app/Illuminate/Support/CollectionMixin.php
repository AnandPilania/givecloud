<?php

namespace Ds\Illuminate\Support;

/** @mixin \Illuminate\Support\Collection */
class CollectionMixin
{
    /**
     * Sort the collection using the given array of values.
     */
    public function sortByValues(): callable
    {
        return function (array $values, $callback = 'id') {
            $callback = $this->valueRetriever($callback);

            return $this->sortBy(function ($item) use ($callback, $values) {
                return array_search($callback($item), $values);
            })->values();
        };
    }
}
