<?php

namespace Ds\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PhoneNumberDestroyFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return is_super_user();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [];
    }
}
