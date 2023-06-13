<?php

namespace Ds\Illuminate\Database\Eloquent;

/** @mixin \Illuminate\Database\Eloquent\Builder */
class BuilderMixin
{
    public function findWithPermission()
    {
        return function ($id, $permissions = 'view', string $url = '/jpanel') {
            return tap($this->findOrFail($id))->userCanOrRedirect($permissions, $url);
        };
    }

    /**
     * Return all distinct values stored in the database
     * for the given column.
     */
    public function getDistinctValuesOf()
    {
        return function ($column, $forceRefresh = false) {
            return $this->getQuery()->getDistinctValuesOf($column, $forceRefresh);
        };
    }
}
