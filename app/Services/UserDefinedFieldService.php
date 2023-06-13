<?php

namespace Ds\Services;

use Ds\Domain\Shared\Exceptions\MessageException;
use Ds\Illuminate\Database\Eloquent\Model;
use Ds\Models\Traits\HasUserDefinedFields;
use Illuminate\Support\Collection;

class UserDefinedFieldService
{
    /**
     * @param array $userDefinedFieldsWithValue [udfKey => udfValue, ...]
     * @return array array of changes ['attached' => [], 'detached' => [], 'updated' => []]
     *
     * @throws \Ds\Domain\Shared\Exceptions\MessageException
     */
    public function synchronize(Model $model, ?array $userDefinedFieldsWithValue = []): array
    {
        if (! $this->modelHasUserDefinedFieldsTrait($model)) {
            throw new MessageException(get_class($model) . ' does not support user defined fields.');
        }

        return $model->userDefinedFields()->sync(
            (new Collection($userDefinedFieldsWithValue))->mapWithKeys(function ($value, $udfKey) {
                return [$udfKey => compact('value')];
            })
        );
    }

    private function modelHasUserDefinedFieldsTrait(Model $model): bool
    {
        return in_array(HasUserDefinedFields::class, array_values(class_uses($model)), true);
    }
}
