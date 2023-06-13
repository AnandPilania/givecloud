<?php

namespace Ds\Illuminate\Database\Eloquent;

interface AuthoritativeDatabase
{
    /**
     * Get the database associated with the model.
     *
     * @return string
     */
    public function getAuthoritativeDatabase();
}
