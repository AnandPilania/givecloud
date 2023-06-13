<?php

namespace Ds\Illuminate\Database\Eloquent;

trait HasAuthoritativeDatabase
{
    /**
     * Get the database associated with the model.
     *
     * @return string
     */
    public function getAuthoritativeDatabase()
    {
        return sys_get('sponsorship_database_name');
    }
}
