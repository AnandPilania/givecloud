<?php

namespace Ds\Common;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;
use ReflectionClass;
use ReflectionProperty;

abstract class DataTransferObject implements Arrayable, Jsonable, JsonSerializable
{
    /**
     * Convert the DTO instance to an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        $data = [];

        $klass = new ReflectionClass(static::class);

        foreach ($klass->getProperties(ReflectionProperty::IS_PUBLIC) as $reflectionProperty) {
            $data[$reflectionProperty->getName()] = $reflectionProperty->getValue($this);
        }

        return $data;
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Get the DTO instance as JSON.
     *
     * @param int $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }
}
