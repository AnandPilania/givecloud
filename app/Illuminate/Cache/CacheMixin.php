<?php

namespace Ds\Illuminate\Cache;

use Closure;
use DateTimeInterface;

/** @mixin \Illuminate\Cache\Repository */
class CacheMixin
{
    /**
     * Check if cache doesn't have a key.
     */
    public function doesNotHave()
    {
        return function (string $key) {
            return $this->has($key) === false;
        };
    }

    /**
     * Cache data until it has been updated.
     */
    public function untilUpdated()
    {
        return function (string $key, DateTimeInterface $ttl, Closure $callback) {
            $entity = $this->get($key);

            if (is_a($entity, CacheEntity::class) && ! $entity->isExpired()) {
                return $entity->data;
            }

            $value = $callback();

            if ($value === null) {
                return $entity->data ?? null;
            }

            $this->forever($key, new CacheEntity($ttl, $value));

            return $value;
        };
    }
}
