<?php

namespace Ds\Domain\Shared\Support;

use BadMethodCallException;
use Closure;
use ReflectionProperty;

class Omniscient
{
    /** @var object */
    protected static $object;

    public function __construct(object $object)
    {
        static::$object = $object;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->invokeAsObject(function () use ($key) {
            return $this->{$key};
        });
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function __set($key, $value)
    {
        return $this->invokeAsObject(function () use ($key, $value) {
            $this->{$key} = $value;
        });
    }

    /**
     * @param string $key
     * @return mixed
     */
    public static function getStatic($key)
    {
        $reflection = new ReflectionProperty(static::$object, $key);
        $reflection->setAccessible(true);

        return $reflection->getValue(null);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public static function setStatic($key, $value)
    {
        $reflection = new ReflectionProperty(static::$object, $key);
        $reflection->setAccessible(true);

        return $reflection->setValue(null, $value);
    }

    /**
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->invokeAsObject(function () use ($method, $parameters) {
            return $this->{$method}(...$parameters);
        });
    }

    /**
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        if (empty(static::$object)) {
            throw new BadMethodCallException;
        }

        $object = static::$object;

        return static::invokeAsStaticObject(static function () use ($object, $method, $parameters) {
            return $object::$method(...$parameters);
        });
    }

    /**
     * @param \Closure $callback
     * @return mixed
     */
    private function invokeAsObject(Closure $callback)
    {
        // use closure binding to allow access to both private properties/methods
        return Closure::bind($callback, static::$object, static::$object)();
    }

    /**
     * @param \Closure $callback
     * @return mixed
     */
    private static function invokeAsStaticObject(Closure $callback)
    {
        // use closure binding to allow access to private properties/methods
        return Closure::bind($callback, null, static::$object)();
    }
}
