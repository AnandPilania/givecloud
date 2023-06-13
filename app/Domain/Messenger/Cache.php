<?php

namespace Ds\Domain\Messenger;

use BotMan\BotMan\Interfaces\CacheInterface;
use Illuminate\Support\Facades\Cache as LaravelCache;

class Cache implements CacheInterface
{
    /**
     * Determine if an item exists in the cache.
     *
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        return $this->getStore()->has($key);
    }

    /**
     * Retrieve an item from the cache by key.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return $this->getStore()->get($key, $default = null);
    }

    /**
     * Retrieve an item from the cache and delete it.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function pull($key, $default = null)
    {
        return $this->getStore()->pull($key, $default);
    }

    /**
     * Store an item in the cache.
     *
     * @param string $key
     * @param mixed $value
     * @param \DateTime|int $minutes
     * @return void
     */
    public function put($key, $value, $minutes)
    {
        $this->getStore()->put($key, $value, now()->addMinutes($minutes));
    }

    /**
     * Get an instance of a Laravel Cache Store.
     *
     * @return \Illuminate\Contracts\Cache\Store
     */
    private function getStore()
    {
        return LaravelCache::store('site');
    }
}
