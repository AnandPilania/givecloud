<?php

namespace Ds\Domain\Theming\Liquid;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Liquid\Drop as LiquidDrop;
use Liquid\LiquidException;

abstract class Drop extends LiquidDrop implements Arrayable, \ArrayAccess, \JsonSerializable
{
    const SOURCE_REQUIRED = true;

    /** @var array */
    protected $blacklist = [
        '__construct',
        'factory',
        'collectionFactory',
        'setContext',
        'overrideProperties',
        'liquidMethodMissing',
        'beforeMethod',
        'hasKey',
        'invokeDrop',
        'resolveData',
        'invokable',
        'getSource',
        'toLiquid',
        'toArray',
        'jsonSerialize',
        '__debugInfo',
        '__toString',
        'offsetExists',
        'offsetGet',
        'offsetSet',
        'offsetUnset',
        'getIterator',
        'count',
        '__get',
    ];

    /** @var array */
    protected $overrides = [];

    /** @var mixed */
    protected $source;

    /** @var array */
    protected $liquid = [];

    /** @var array */
    protected $attributes = [];

    /** @var array */
    protected $mutators = [];

    /** @var array */
    protected $invocationCache = [];

    /** @var array */
    protected $serializationBlacklist = [];

    /**
     * Create an instance.
     *
     * @param \Illuminate\Database\Eloquent\Model|null $source
     * @param array $overrides
     */
    public function __construct(Model $source = null, array $overrides = [])
    {
        $this->source = $source;
        $this->overrides = $overrides;

        $this->initialize($source);

        foreach ($this->attributes as $key) {
            $this->liquid[$key] = $source->getAttribute($key);
        }
    }

    /**
     * Create a collection of Drop instance.
     *
     * @param \Illuminate\Support\Collection $collection
     * @param string $klass
     * @param array $overrides
     * @return \Illuminate\Support\Collection
     */
    public static function collectionFactory(Collection $collection = null, $klass, array $overrides = []): Collection
    {
        return $collection->map(function ($source) use ($klass, $overrides) {
            return static::factory($source, $klass, $overrides);
        });
    }

    /**
     * Create a Drop instance.
     *
     * @param \Illuminate\Database\Eloquent\Model|\stdClass|array|null $source
     * @param string $klass
     * @param array $overrides
     * @return \Ds\Domain\Theming\Liquid\Drop
     */
    public static function factory($source = null, $klass, array $overrides = []): Drop
    {
        $klass = "\\Ds\\Domain\\Theming\\Liquid\\Drops\\{$klass}Drop";

        if (! class_exists($klass)) {
            throw new LiquidException("Bad drop [$klass]");
        }

        if (! $source && $klass::SOURCE_REQUIRED) {
            return new Drops\EmptyDrop;
        }

        return new $klass($source, $overrides);
    }

    /**
     * Initialize the drop.
     *
     * @param \Illuminate\Database\Eloquent\Model|null $source
     */
    protected function initialize($source)
    {
        // do nothing
    }

    /**
     * Catch all method that is invoked before a specific method
     *
     * @param string $method
     * @return mixed
     */
    protected function liquidMethodMissing($method)
    {
        if ($this->source && in_array($method, $this->mutators)) {
            return $this->source->getAttribute($method);
        }

        return $this->liquid[$method] ?? null;
    }

    /**
     * Invoke a specific method
     *
     * @param string $method
     * @return mixed
     */
    public function invokeDrop($method)
    {
        if (array_key_exists($method, $this->overrides)) {
            return $this->overrides[$method];
        }

        if (array_key_exists($method, $this->invocationCache)) {
            return $this->invocationCache[$method];
        }

        if ($this->invokable($method)) {
            $result = $this->{$method}();
        } else {
            $result = $this->liquidMethodMissing($method);
        }

        return $this->invocationCache[$method] = static::resolveData($result);
    }

    /**
     * Resolve data to Liquid-safe types.
     *
     * @param mixed $data
     * @param bool $strict
     * @return mixed
     */
    public static function resolveData($data, $strict = true)
    {
        if (is_object($data)) {
            if ($data instanceof self) {
                return $data;
            }

            if ($data instanceof \DateTime) {
                return toUtcFormat($data, 'api');
            }

            if ($data instanceof \Ds\Domain\Theming\Liquid\Liquidable) {
                return static::resolveData($data->toLiquid(), $strict);
            }

            if ($data instanceof \Ds\Illuminate\Http\Resources\Json\JsonResource) {
                return static::resolveData($data->toObject(), $strict);
            }

            if ($data instanceof \Illuminate\Support\Collection) {
                return static::resolveData($data->all(), $strict);
            }

            if ($data instanceof \Illuminate\Pagination\LengthAwarePaginator) {
                return new Drops\PaginateDrop($data);
            }

            if ($data instanceof \Illuminate\Support\ViewErrorBag) {
                return static::resolveData($data->toArray(), $strict);
            }

            if ($data instanceof \Illuminate\Contracts\Support\Arrayable) {
                return static::resolveData($data->toArray(), $strict);
            }

            if ($data instanceof \Traversable) {
                return static::resolveData(iterator_to_array($data), $strict);
            }

            if ($data instanceof \stdClass) {
                return static::resolveData((array) $data, $strict);
            }

            if ($strict) {
                throw new LiquidException('Unsupported object [' . get_class($data) . '].');
            }
        }

        if (is_array($data)) {
            foreach ($data as &$item) {
                $item = static::resolveData($item, $strict);
            }
        }

        return $data;
    }

    /**
     * Check for method existence.
     *
     * @param string $method
     * @return bool
     */
    public function invokable($method)
    {
        if (method_exists($this, $method) && ! in_array($method, $this->blacklist)) {
            return true;
        }

        return false;
    }

    /**
     * Override specific properties.
     *
     * @param array $properties
     */
    public function overrideProperties(array $properties)
    {
        if (! Arr::isAssoc($properties)) {
            $properties = array_combine(
                array_values($properties),
                array_fill(0, count($properties), null)
            );
        }

        $this->overrides = array_merge($this->overrides, $properties);
    }

    /**
     * Get the source.
     *
     * @return mixed
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        $data = [];

        $methodKeys = array_merge(
            array_keys($this->liquid),
            $this->mutators
        );

        $klass = new \ReflectionClass($this);
        foreach ($klass->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            if (! in_array($method->name, $this->blacklist)) {
                $methodKeys[] = $method->name;
            }
        }

        foreach ($methodKeys as $method) {
            if (
                array_key_exists($method, $this->overrides)
                || array_key_exists($method, $this->invocationCache)
                || ! in_array($method, $this->serializationBlacklist)
            ) {
                $data[$method] = $this->invokeDrop($method);
            }
        }

        return $data;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Dump the drop.
     *
     * @return array
     */
    public function __debugInfo()
    {
        return $this->toArray();
    }

    /**
     * Check if property exists.
     *
     * @param string $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return isset($this->{$offset});
    }

    /**
     * Retrieve property.
     *
     * @param string $offset
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->invokeDrop($offset);
    }

    /**
     * Set a property.
     *
     * @param string $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value): void
    {
        // do nothing
    }

    /**
     * Unset a property.
     *
     * @param string $offset
     */
    public function offsetUnset($offset): void
    {
        // do nothing
    }

    /**
     * Retrieve property.
     *
     * @param string $key
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    public function __get($key)
    {
        return $this->invokeDrop($key);
    }

    /**
     * Return string representation of drop.
     *
     * @return string
     */
    public function __toString()
    {
        $klass = str_replace('Ds\\Domain\\Theming\\Liquid\\Drops\\', '', get_class($this));

        if ($this->source) {
            return "$klass#" . $this->source->getKey();
        }

        return $klass;
    }
}
