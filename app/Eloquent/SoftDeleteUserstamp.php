<?php

namespace Ds\Eloquent;

/** @phan-file-suppress PhanUndeclaredMethod */
/** @phan-file-suppress PhanUndeclaredStaticMethod */
trait SoftDeleteUserstamp
{
    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    public static function bootSoftDeleteUserstamp()
    {
        static::deleting(function ($model) {
            $model->setDeletedBy($model->freshUserstamp());
            $model->saveQuietly();
        });
    }

    /**
     * Set the value of the "created by" attribute.
     *
     * @param mixed $value
     * @return void
     */
    public function setDeletedBy($value)
    {
        $this->{$this->getDeletedByColumn()} = $value;
    }

    /**
     * Get the name of the "created by" column.
     *
     * @return string
     */
    public function getDeletedByColumn()
    {
        return defined('static::DELETED_BY') ? static::DELETED_BY : 'deleted_by';
    }

    /**
     * Get a fresh timestamp for the model.
     *
     * @return int|null
     */
    public function freshUserstamp()
    {
        return user('id');
    }

    /**
     * Relationship: Deleted By
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function deletedBy()
    {
        return $this->belongsTo('Ds\Models\User', $this->getDeletedByColumn());
    }
}
