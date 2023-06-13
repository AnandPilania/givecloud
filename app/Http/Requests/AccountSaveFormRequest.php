<?php

namespace Ds\Http\Requests;

use Ds\Models\Member as Account;
use Illuminate\Validation\Rule;

class AccountSaveFormRequest extends Request
{
    use FlashFailedValidationTrait;

    protected $failedValidationDefaultFlashMessage = 'An error occurred while saving the account.';

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => ['nullable', Rule::unique(Account::class)->ignore($this->input('id'))],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'email.unique' => 'The email is already in use by another supporter.',
        ];
    }
}
