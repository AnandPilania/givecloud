<?php

namespace Ds\Eloquent;

use ArrayAccess;
use Ds\Domain\Theming\Liquid\Drop;
use Ds\Domain\Theming\Liquid\Liquidable;
use Illuminate\Contracts\Support\Arrayable;

class MetadataCollection implements ArrayAccess, Arrayable, Liquidable
{
    /** @var \Ds\Eloquent\Metadatable */
    private $model;

    /**
     * Create a new collection.
     *
     * @param \Ds\Eloquent\Metadatable $model
     */
    public function __construct(Metadatable $model)
    {
        $this->model = $model;
    }

    /**
     * Dump the metadata.
     *
     * @return array
     */
    public function __debugInfo()
    {
        return $this->toArray();
    }

    /**
     * Get the collection of items as a plain array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->model->metadata();
    }

    /**
     * Get the collection of items as a plain array.
     *
     * @return \Ds\Domain\Theming\Liquid\Drop
     */
    public function toLiquid()
    {
        return Drop::factory($this->model->getMetadata(), 'MetadataCollection');
    }

    /**
     * Determine if an item exists at an offset.
     *
     * @param mixed $key
     * @return bool
     */
    public function offsetExists($key): bool
    {
        return (bool) $this->model->getMetadata($key);
    }

    /**
     * Get an item at a given offset.
     *
     * @param mixed $key
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($key)
    {
        return $this->model->metadata($key);
    }

    /**
     * Set the item at a given offset.
     *
     * @param mixed $key
     * @param mixed $value
     * @return void
     */
    public function offsetSet($key, $value): void
    {
        if (is_null($key)) {
            $this->model->metadata = $value;
        } else {
            $this->model->setMetadata($key, $value);
        }
    }

    /**
     * Unset the item at a given offset.
     *
     * @param string $key
     * @return void
     */
    public function offsetUnset($key): void
    {
        $item = $this->model->getMetadata($key);

        if ($item) {
            $item->markForDeletion();
        }
    }

    /**
     * Get an item with a given key.
     *
     * @param mixed $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->model->metadata($key);
    }

    /**
     * Set an item with a given key and value.
     *
     * @param mixed $key
     * @param mixed $value
     */
    public function __set($key, $value)
    {
        $this->offsetSet($key, $value);
    }

    /**
     * Get an item with a given key.
     *
     * @param mixed $key
     * @param mixed $defaultValue
     * @return mixed
     */
    public function __invoke($key, $defaultValue = null)
    {
        return $this->model->metadata($key, $defaultValue);
    }
}
