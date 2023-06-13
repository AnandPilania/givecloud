<?php

namespace Ds\Http\Requests;

use Illuminate\Contracts\Validation\Validator;

trait FlashFailedValidationTrait
{
    /**
     * Handle a failed validation attempt.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function failedValidation(Validator $validator): void
    {
        $this->container['flash']->error($validator->errors()->all()[0] ?? $this->getFailedValidationDefaultFlashMessage());

        parent::failedValidation($validator);
    }

    protected function getFailedValidationDefaultFlashMessage(): string
    {
        return $this->failedValidationDefaultFlashMessage ?? 'An error occurred while saving.';
    }
}
