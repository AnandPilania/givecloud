<?php

namespace Ds\Http\Requests;

use Ds\Domain\Shared\Exceptions\ForbiddenActionException;

class PersonalAccessTokenDestroyFormRequest extends Request
{
    use FlashFailedValidationTrait;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->getKey() === $this->route('token')->user_id;
    }

    protected function failedAuthorization()
    {
        throw new ForbiddenActionException("You cannot amend someone else's personal access token.");
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [];
    }
}
