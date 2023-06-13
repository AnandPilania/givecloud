<?php

namespace Ds\Illuminate\Validation\Rules;

use Illuminate\Contracts\Validation\Rule;

class ProfanityRule implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (! sys_get('bool:fundraising_pages_profanity_filter')) {
            return true;
        }

        $details = app('profanityFilter')->filter($value, true);

        return ! data_get($details, 'hasMatch', false);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('validation.profanity');
    }
}
