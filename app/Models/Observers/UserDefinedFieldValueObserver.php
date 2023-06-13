<?php

namespace Ds\Models\Observers;

use Ds\Models\UserDefinedFieldValue;

class UserDefinedFieldValueObserver
{
    public function creating(UserDefinedFieldValue $model): bool
    {
        return $this->validateOption($model->userDefinedField->getOptions(), $model->value);
    }

    private function validateOption(array $userDefinedFieldsOptions = [], $value = null): bool
    {
        if (empty($userDefinedFieldsOptions)) {
            return true;
        }

        if (is_array($value)) {
            $validOptions = array_filter($value, function ($val) use ($userDefinedFieldsOptions) {
                return array_key_exists($val, $userDefinedFieldsOptions);
            });

            // check all values are valid options.
            return count($value) === count($validOptions);
        }

        return array_key_exists($value, $userDefinedFieldsOptions);
    }
}
