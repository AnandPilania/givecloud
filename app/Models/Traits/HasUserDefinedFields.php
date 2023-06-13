<?php

namespace Ds\Models\Traits;

use Ds\Models\UserDefinedField;
use Ds\Models\UserDefinedFieldValue;
use Ds\Services\UserDefinedFieldService;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasUserDefinedFields
{
    public function userDefinedFields(): MorphToMany
    {
        return $this->morphToMany(UserDefinedField::class, 'user_defined_fieldable')
            ->using(UserDefinedFieldValue::class)
            ->withPivot('value')
            ->withTimestamps();
    }

    /**
     * @return array ['attached' => [], 'detached' => [], 'updated' => []]
     *
     * @throws \Exception
     */
    public function syncUserDefinedFields(?array $userDefinedFieldsToSync = []): array
    {
        /** @var \Ds\Services\UserDefinedFieldService */
        $userDefinedFieldService = app(UserDefinedFieldService::class);

        return $userDefinedFieldService->synchronize($this, $userDefinedFieldsToSync);
    }
}
