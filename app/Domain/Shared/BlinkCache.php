<?php

namespace Ds\Domain\Shared;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class BlinkCache
{
    /** @var \Illuminate\Support\Collection */
    private $items;

    /**
     * Create an instance.
     */
    public function __construct()
    {
        $this->flush();
    }

    /**
     * Determine if an item exists in the collection by key.
     *
     * @param mixed $key
     * @return bool
     */
    public function has($key)
    {
        return $this->items->has($key);
    }

    /**
     * Retrieve an item from the cache by key.
     *
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        if (Str::contains($key, '*')) {
            $pattern = '/' . str_replace('\*', '*', preg_quote($key)) . '/';

            return $this->items->first(function ($value, $key) use ($pattern) {
                return preg_match($pattern, $key) === 1;
            });
        }

        return $this->items->get($key);
    }

    /**
     * Store an item in the cache.
     *
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    public function put($key, $value)
    {
        if (is_callable($value)) {
            $value = $value();
        }

        $this->items->put($key, $value);

        return $value;
    }

    /**
     * Increment the value of an item in the cache.
     *
     * @param string $key
     * @param mixed $value
     * @return int
     */
    public function increment($key, $value = 1)
    {
        $value = ((int) $this->get($key)) + $value;

        return $this->put($key, $value);
    }

    /**
     * Decrement the value of an item in the cache.
     *
     * @param string $key
     * @param mixed $value
     * @return int
     */
    public function decrement($key, $value = 1)
    {
        return $this->increment($key, $value * -1);
    }

    /**
     * Store and retrieve an item in the cache.
     *
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    public function remember($key, $value)
    {
        if (is_callable($value) && $this->items->has($key)) {
            return $this->items->get($key);
        }

        return $this->put($key, $value);
    }

    /**
     * Remove an item from the cache.
     *
     * @param string|array $key
     * @return bool
     */
    public function forget($key)
    {
        if (is_array($key)) {
            $keys = $key;
        } elseif (Str::contains($key, '*')) {
            $pattern = '/^' . str_replace('\*', '.*', preg_quote($key)) . '$/';

            $keys = $this->items->keys()->filter(function ($key) use ($pattern) {
                return preg_match($pattern, $key) === 1;
            })->values();
        } else {
            $keys = [$key];
        }

        foreach ($keys as $key) {
            $this->items->forget($key);
        }

        return true;
    }

    /**
     * Remove all items from the cache.
     *
     * @return bool
     */
    public function flush()
    {
        $this->items = new Collection;

        return true;
    }

    /**
     * Retrieve all items from the cache.
     *
     * @return array
     */
    public function all()
    {
        return $this->items->all();
    }

    /**
     * Return debug info.
     *
     * @return array
     */
    public function __debugInfo()
    {
        return [
            'keys' => $this->items->count(),
        ];
    }
}
