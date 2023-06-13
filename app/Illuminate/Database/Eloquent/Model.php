<?php

namespace Ds\Illuminate\Database\Eloquent;

use DateTimeInterface;
use Ds\Domain\Shared\Date;
use Ds\Eloquent\Metadatable;
use Ds\Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use InvalidArgumentException;

abstract class Model extends EloquentModel
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var string[]|bool
     */
    protected $guarded = ['id'];

    /**
     * The attributes that are not arrayable.
     *
     * @var array
     */
    protected $notArrayable = [];

    /**
     * An array to map relation names to their morph names in the database.
     *
     * @var array
     */
    public $relationMorphMap = [];

    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    public static function table()
    {
        return (new static)->getTable();
    }

    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    public function getTable()
    {
        $table = parent::getTable();

        if ($this instanceof AuthoritativeDatabase) {
            $database = $this->getAuthoritativeDatabase();

            if ($database) {
                return "$database.$table";
            }
        }

        return $table;
    }

    /**
     * Set the table associated with the model.
     *
     * @param string $table
     * @return $this
     */
    public function setTable($table)
    {
        if ($this instanceof AuthoritativeDatabase) {
            $database = $this->getAuthoritativeDatabase();

            // Starting in L57 the newInstance method makes the
            // following call:
            //
            //   $model->setTable($this->getTable());
            //
            // This resulted in the authoritative database prefix
            // being recursively concatenated to the table name

            if (Str::startsWith($table, "$database.")) {
                $table = Str::after($table, "$database.");
            }
        }

        return parent::setTable($table);
    }

    /**
     * Eager load relations on the model if they are already eager loaded.
     *
     * @param array|string $relations
     * @return $this
     */
    public function loadLoaded($relations)
    {
        $relations = is_string($relations) ? func_get_args() : $relations;

        foreach ($relations as $relation) {
            if (array_key_exists($relation, $this->relations)) {
                $this->load($relation);
            }
        }

        return $this;
    }

    /**
     * Update the model's update timestamp.
     *
     * @return bool
     */
    public function touch()
    {
        if (method_exists($this, 'updateUserstamps')) {
            $this->updateUserstamps();
        }

        return parent::touch();
    }

    /**
     * Return a timestamp as DateTime object.
     *
     * @param mixed $value
     * @return \Illuminate\Support\Carbon
     */
    protected function asDateTime($value)
    {
        try {
            return parent::asDateTime($value);
        } catch (InvalidArgumentException $e) {
            return Carbon::parse($value);
        }
    }

    /**
     * Return a timestamp as Date object with time set to 00:00:00.
     *
     * @param mixed $value
     * @return \Ds\Domain\Shared\Date
     */
    protected function asDate($value)
    {
        return Date::instance($this->asDateTime($value));
    }

    /**
     * Convert the model's attributes to an array.
     *
     * @return array
     */
    public function attributesToArray()
    {
        $attributes = parent::attributesToArray();

        if (method_exists($this, 'addMetadataAttributesToArray')) {
            $attributes = $this->addMetadataAttributesToArray($attributes);
        }

        return $attributes;
    }

    /**
     * Get an attribute array of all arrayable values.
     *
     * @param array $values
     * @return array
     */
    protected function getArrayableItems(array $values)
    {
        if (count($this->notArrayable) > 0) {
            $values = array_diff_key($values, array_flip($this->notArrayable));
        }

        return parent::getArrayableItems($values);
    }

    /**
     * Prepare a date for array / JSON serialization.
     *
     * @param \DateTimeInterface $date
     * @return string
     */
    protected function serializeDate(DateTimeInterface $date)
    {
        if ($date instanceof Date) {
            return formatDateTime($date, 'date');
        }

        return $date->format($this->getDateFormat());
    }

    /**
     * Set the model's original attribute values.
     *
     * @param string $key
     * @param mixed $default
     * @return $this
     */
    public function setOriginal($key, $default = null)
    {
        Arr::set($this->original, $key, $default);

        return $this;
    }

    /**
     * Convert the model instance to stdClass.
     *
     * @return \stdClass
     */
    public function toObject()
    {
        return json_decode($this->toJson());
    }

    public function getContentDigest(): string
    {
        // intentionally not using toArray/toJson/json_encode directly on the
        // model to avoid any potential recursion if the digest in included
        $content = json_encode($this->attributes);

        if ($this instanceof Metadatable) {
            $content .= json_encode($this->getMetadata()->map(fn ($m) => $m->getAttributeFromArray('value')));
        }

        return sha1($content);
    }

    /**
     * Get a debug identifier for model instance.
     *
     * @return string
     */
    public function getDebugIdentifier()
    {
        // return spl_object_hash($this);

        $klass = str_replace('Ds\\Models\\', '', get_class($this));

        return $this->exists ? "{$klass}#{$this->id}" : $klass;
    }

    /**
     * Store value in the Request cache.
     *
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    protected function reqcache($key, $value)
    {
        $key = $this->getTable() . ':' . $this->getKey() . ':' . Str::snake($key);

        return reqcache($key, $value);
    }

    /**
     * @throws \Throwable
     */
    public static function createOrFail(array $attributes = [], array $options = []): self
    {
        return tap(new static($attributes))->saveOrFail($options);
    }

    /**
     * @throws \Throwable
     */
    public function updateOrFail(array $attributes = [], array $options = []): self
    {
        if (! $this->exists) {
            throw (new ModelNotFoundException)->setModel(static::class);
        }

        return tap($this->fill($attributes))->saveOrFail($options);
    }

    /**
     * Instantiate a new MorphToMany relationship.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Illuminate\Database\Eloquent\Model $parent
     * @param string $name
     * @param string $table
     * @param string $foreignPivotKey
     * @param string $relatedPivotKey
     * @param string $parentKey
     * @param string $relatedKey
     * @param string|null $relationName
     * @param bool $inverse
     * @return \Ds\Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    protected function newMorphToMany(Builder $query, EloquentModel $parent, $name, $table, $foreignPivotKey, $relatedPivotKey, $parentKey, $relatedKey, $relationName = null, $inverse = false)
    {
        return new MorphToMany($query, $parent, $name, $table, $foreignPivotKey, $relatedPivotKey, $parentKey, $relatedKey, $relationName, $inverse);
    }
}
