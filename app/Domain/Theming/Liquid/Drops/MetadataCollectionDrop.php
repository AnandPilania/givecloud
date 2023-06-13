<?php

namespace Ds\Domain\Theming\Liquid\Drops;

use Ds\Domain\Theming\Liquid\Drop;
use Illuminate\Support\Collection;

class MetadataCollectionDrop extends Drop
{
    /** @var \Illuminate\Support\Collection */
    protected $source;

    public function __construct(Collection $source)
    {
        $this->source = Drop::collectionFactory($source->keyBy('key'), 'Metadata');
    }

    /**
     * Catch all method that is invoked before a specific method
     *
     * @param string $method
     * @return mixed
     */
    protected function liquidMethodMissing($method)
    {
        return $this->source[$method] ?? null;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->source->map->toLiquid();
    }
}
