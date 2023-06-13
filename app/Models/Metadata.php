<?php

namespace Ds\Models;

use Ds\Domain\Theming\Liquid\Liquidable;
use Ds\Illuminate\Database\Eloquent\Factories\HasFactory;
use Ds\Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Metadata extends Model implements Liquidable
{
    use HasFactory;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'encrypted' => 'boolean',
    ];

    /** @var bool */
    protected $markForDeletion = false;

    public function metadatable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Mark data for deletion.
     */
    public function markForDeletion()
    {
        $this->markForDeletion = true;
    }

    /**
     * Has data been marked for deletion
     *
     * @return bool
     */
    public function isMarkedForDeletion()
    {
        return $this->markForDeletion;
    }

    /**
     * Set the value and type.
     *
     * @param mixed $value
     * @param bool $encrypt
     * @param string $type
     */
    public function setValue($value, $encrypt = false, $type = null)
    {
        if ($type === null) {
            $this->type = $this->getTypeFromValue($value);
        } else {
            $this->type = $type;
        }

        $this->attributes['value'] = $this->serializeValue($value, $this->type);

        if ($encrypt) {
            $this->encrypted = true;
            $this->attributes['value'] = encrypt($this->attributes['value']);
        }

        $this->markForDeletion = false;
    }

    /**
     * Set the value and type.
     *
     * @param mixed $value
     * @param mixed $type
     */
    public function setEncryptedValue($value, $type = false)
    {
        $this->setValue($value, true, $type);
    }

    /**
     * Attribute Mutator: Set the value and type.
     *
     * @param mixed $value
     */
    public function setValueAttribute($value)
    {
        $this->setValue($value);
    }

    /**
     * Get the value.
     *
     * @param string|null $type
     * @return mixed
     */
    public function getValue($type = null)
    {
        $value = $this->attributes['value'];

        if ($this->encrypted) {
            $value = decrypt($value);
        }

        return $this->deserializeValue($value, $type ?? $this->type);
    }

    /**
     * Attribute Mask: Get the value.
     *
     * @param string $value
     * @return mixed
     */
    public function getValueAttribute($value)
    {
        return $this->getValue();
    }

    /**
     * Select a type for a value.
     *
     * @param mixed $value
     * @return string
     */
    protected function getTypeFromValue($value)
    {
        if (is_array($value)) {
            return 'array';
        }

        if (is_bool($value)) {
            return 'boolean';
        }

        if (is_integer($value)) {
            return 'integer';
        }

        if (is_float($value)) {
            return 'float';
        }

        if (is_null($value)) {
            return 'null';
        }

        if ($value instanceof \Illuminate\Support\Collection) {
            return 'collection';
        }

        if ($value instanceof \Ds\Domain\Shared\Date) {
            return 'date';
        }

        if ($value instanceof \DateTimeInterface) {
            return 'datetime';
        }

        if ($value instanceof \Illuminate\Database\Eloquent\Model) {
            return 'model';
        }

        if ($value instanceof \Illuminate\Contracts\Support\Arrayable) {
            return 'array';
        }

        if (is_object($value)) {
            return 'object';
        }

        return 'string';
    }

    /**
     * Serialize the value.
     *
     * @param mixed $value
     * @param string $type
     * @return mixed
     */
    protected function serializeValue($value, $type = 'string')
    {
        switch ($type) {
            case 'boolean':
                return (bool) $value;
            case 'array':
            case 'collection':
                if (is_array($value)) {
                    // make the assumption that an array with a single NULL value is the
                    // result of submitting a form with an array field with no value. for
                    // example "metadata[some_options][]". this is something selectize and
                    // other form libraries do when a field with initial values is
                    // submitted with no values.
                    if (count($value) === 1 && $value[0] === null) {
                        $value = [];
                    }

                    return $this->asJson($value);
                }

                if ($value instanceof \Illuminate\Support\Collection) {
                    return $value->toJson();
                }

                if ($value instanceof \Illuminate\Contracts\Support\Arrayable) {
                    return $this->asJson($value->toArray());
                }

                if ($value instanceof \Traversable) {
                    return $this->asJson(iterator_to_array($value));
                }

                throw new \InvalidArgumentException;
            case 'csv':
                if (is_array($value)) {
                    // do nothing
                } elseif ($value instanceof \Illuminate\Contracts\Support\Arrayable) {
                    $value = $value->toArray();
                } elseif ($value instanceof \Traversable) {
                    $value = iterator_to_array($value);
                } else {
                    throw new \InvalidArgumentException;
                }

                return str_putcsv($value);
            case 'date':
            case 'datetime':
                return toUtcFormat($value, 'api');
            case 'float':
                return (float) $value;
            case 'integer':
                return (int) $value;
            case 'model':
                if ($value instanceof \Illuminate\Database\Eloquent\Model) {
                    return get_class($value) . ($value->exists ? '#' . $value->getKey() : '');
                }

                throw new \InvalidArgumentException;
            case 'null':
                return null;
            case 'object':
                if (is_object($value)) {
                    return $this->asJson($value);
                }

                throw new \InvalidArgumentException;
            case 'string':
                return (string) $value;
        }

        return (string) $value;
    }

    /**
     * Deserialize the value.
     *
     * @param string $value
     * @param string $type
     * @return mixed
     */
    protected function deserializeValue($value, $type = 'string')
    {
        switch ($type) {
            case 'array':
                return $this->fromJson($value);
            case 'boolean':
                return (bool) $value;
            case 'collection':
                return collect($this->fromJson($value));
            case 'csv':
                return str_getcsv($value);
            case 'date':
                return $this->asDate($value);
            case 'datetime':
                return $this->asDateTime($value);
            case 'float':
                return (float) $value;
            case 'integer':
                return (int) $value;
            case 'model':
                if (strpos($value, '#') === false) {
                    return new $value;
                }
                [$klass, $id] = explode('#', $value, 2);

                return (new $klass)->findOrFail($id);
            case 'null':
                return null;
            case 'object':
                return $this->fromJson($value, true);
            case 'string':
                return (string) $value;
        }

        return (string) $value;
    }

    /**
     * Liquid representation of model.
     */
    public function toLiquid()
    {
        return \Ds\Domain\Theming\Liquid\Drop::factory($this, 'Metadata');
    }
}
