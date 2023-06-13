<?php

namespace Ds\Eloquent;

use Ds\Models\Metadata;

/** @mixin \Ds\Illuminate\Database\Eloquent\Model */
trait HasMetadata
{
    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function bootHasMetadata()
    {
        static::saved(function (self $model) {
            $model->updateMetadataAttributes();
        });
    }

    /**
     * Relationship: Metadata
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function metadataRelation()
    {
        return $this->morphMany(Metadata::class, 'metadatable');
    }

    /**
     * Helper for getting and setting metadata value(s).
     *
     * @param string|array $key
     * @param mixed $defaultValue
     * @return mixed
     */
    public function metadata($key = null, $defaultValue = null)
    {
        if (is_array($key)) {
            return $this->setMetadata($key);
        }

        if ($key) {
            if (strpos($key, ':') === false) {
                $type = null;
            } else {
                [$type, $key] = explode(':', $key, 2);
            }

            if ($model = $this->getMetadata($key)) {
                return $model->getValue($type) ?? $defaultValue;
            }

            return $defaultValue;
        }

        return $this->getMetadata()->pluck('value', 'key')->toArray();
    }

    /**
     * Get metadata.
     *
     * @param string|null $key
     * @return \Ds\Models\Metadata|null
     */
    public function getMetadata($key = null)
    {
        $metadata = $this->metadataRelation->reject(function ($item) {
            return $item->isMarkedForDeletion();
        });

        if ($key) {
            return $metadata->firstWhere('key', $key);
        }

        return $metadata;
    }

    /**
     * Set metadata value(s).
     *
     * @param string|array $key
     * @param mixed $value
     */
    public function setMetadata($key, $value = null)
    {
        if (is_array($key)) {
            collect($key)->each(function ($item, $key) {
                $this->setMetadata($key, $item);
            });
        } else {
            if (strpos($key, ':') === false) {
                $type = null;
            } else {
                [$type, $key] = explode(':', $key, 2);
            }

            $model = $this->metadataRelation->firstWhere('key', $key);

            if (empty($model)) {
                $model = new Metadata;
                $model->metadatable_id = $this->getKey();
                $model->metadatable_type = $this->getMorphClass();
                $model->key = $key;

                $this->metadataRelation->add($model);
            }

            $model->setValue($value, false, $type);
        }
    }

    /**
     * Attribute Mask: Metadata.
     *
     * @return \Ds\Eloquent\MetadataCollection
     */
    public function getMetadataAttribute()
    {
        return new MetadataCollection($this);
    }

    /**
     * Attribute Mutator: Metadata.
     *
     * @param array $value
     */
    public function setMetadataAttribute(array $value)
    {
        $this->metadataRelation->each(function ($item) {
            $item->markForDeletion();
        });

        return $this->setMetadata($value);
    }

    /**
     *  Update the metadata attributes.
     */
    public function updateMetadataAttributes()
    {
        foreach ($this->metadataRelation as $model) {
            if ($model->isMarkedForDeletion()) {
                $model->delete();

                continue;
            }

            // set the polymorphic relationship keys here to catch cases
            // where metadata was added to a model before it was saved
            if ($model->isDirty()) {
                $model->metadatable_id = $this->getKey();
                $model->metadatable_type = $this->getMorphClass();
                $model->save();
            }
        }
    }

    /**
     * Add the metadata attributes to the attributes array.
     *
     * @param array $attributes
     * @return array
     */
    public function addMetadataAttributesToArray($attributes)
    {
        $this->makeHidden('metadataRelation');

        if ($this->relationLoaded('metadataRelation')) {
            $attributes['metadata'] = $this->metadata();
        }

        return $attributes;
    }

    /**
     * Scope: Join Metadata
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param string $alias
     */
    public function scopeJoinMetadata($query, $alias = 'metadata')
    {
        $table = Metadata::table();
        $klass = $this->getMorphClass();

        return $query->join(
            "$table AS $alias",
            function ($join) use ($alias, $klass) {
                $join->on($this->getQualifiedKeyName(), '=', "$alias.metadatable_id")
                    ->where("$alias.metadatable_type", '=', $klass);
            }
        );
    }
}
