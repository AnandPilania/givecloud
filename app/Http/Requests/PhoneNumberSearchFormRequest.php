<?php

namespace Ds\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PhoneNumberSearchFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return is_super_user();
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'area_code.required_if' => 'Area code is required for local phone numbers.',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'area_code' => [
                'required_if:type,local',
                'nullable',
                'string',
            ],
            'country' => [
                'required',
                Rule::in(array_keys(cart_countries())),
            ],
            'type' => 'required|in:local,toll-free',
        ];
    }

    public function isTollFree(): bool
    {
        return $this->type === 'toll-free';
    }
}
