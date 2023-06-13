<?php

namespace Ds\Eloquent;

use Ds\Illuminate\Database\Eloquent\AuthoritativeDatabase;

/** @mixin \Ds\Illuminate\Database\Eloquent\Model */
trait Userstamps
{
    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    public static function bootUserstamps()
    {
        static::saving(function ($model) {
            $model->updateUserstamps();
        });

        static::creating(function ($model) {
            $model->updateUserstamps();
        });
    }

    /**
     * Update the creation and update userstamps.
     *
     * @return void
     */
    protected function updateUserstamps()
    {
        // Skip updating userstamps for sponsorship models that have a database
        if ($this instanceof AuthoritativeDatabase && $this->getAuthoritativeDatabase()) {
            return;
        }

        $user = $this->_freshUserstamp();

        if (! $this->isDirty($this->getUpdatedByColumn())) {
            $this->setUpdatedBy($user);
        }

        if (! $this->exists && ! $this->isDirty($this->getCreatedByColumn())) {
            $this->setCreatedBy($user);
        }
    }

    /**
     * Set the value of the "created by" attribute.
     *
     * @param mixed $value
     * @return void
     */
    public function setCreatedBy($value)
    {
        $this->{$this->getCreatedByColumn()} = $value;
    }

    /**
     * Set the value of the "updated by" attribute.
     *
     * @param mixed $value
     * @return void
     */
    public function setUpdatedBy($value)
    {
        $this->{$this->getUpdatedByColumn()} = $value;
    }

    /**
     * Get the name of the "created by" column.
     *
     * @return string
     */
    public function getCreatedByColumn()
    {
        return defined('static::CREATED_BY') ? static::CREATED_BY : 'created_by';
    }

    /**
     * Get the name of the "updated by" column.
     *
     * @return string
     */
    public function getUpdatedByColumn()
    {
        return defined('static::UPDATED_BY') ? static::UPDATED_BY : 'updated_by';
    }

    /**
     * Get a fresh timestamp for the model.
     *
     * @return int|null
     */
    public function _freshUserstamp()
    {
        return user('id') ?? 1;
    }

    /**
     * Relationship: Created By
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function createdBy()
    {
        return $this->belongsTo('Ds\Models\User', $this->getCreatedByColumn())->withTrashed();
    }

    /**
     * Relationship: Updated By
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function updatedBy()
    {
        return $this->belongsTo('Ds\Models\User', $this->getUpdatedByColumn())->withTrashed();
    }
}
