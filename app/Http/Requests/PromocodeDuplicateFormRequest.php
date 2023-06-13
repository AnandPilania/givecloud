<?php

namespace Ds\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PromocodeDuplicateFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return user()->can('promocode.add');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'new_code' => 'required|string|unique:Ds\Models\PromoCode,code',
        ];
    }

    /**
     * Send the right messages back to the user.
     */
    public function messages()
    {
        return [
            'new_code.unique' => 'Duplication failed. That promotional code is already in use. Please try a different code.',
        ];
    }
}
