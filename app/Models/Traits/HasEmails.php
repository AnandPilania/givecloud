<?php

namespace Ds\Models\Traits;

use Ds\Models\Email;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasEmails
{
    public function emails(): MorphToMany
    {
        return $this->morphToMany(Email::class, 'emailable');
    }
}
