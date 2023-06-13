<?php

namespace Ds\Models\Traits;

use Ds\Models\ExternalReference;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasExternalReferences
{
    public function references(): MorphMany
    {
        return $this->morphMany(ExternalReference::class, 'referenceable');
    }
}
