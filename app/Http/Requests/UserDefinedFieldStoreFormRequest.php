<?php

namespace Ds\Http\Requests;

use Ds\Models\UserDefinedField;
use Illuminate\Foundation\Http\FormRequest;

class UserDefinedFieldStoreFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'entity' => 'required|string|in:' . implode(',', UserDefinedField::ENTITIES),
            'field_attributes' => 'required|array',
            'field_type' => 'required|string|in:' . implode(',', UserDefinedField::FIELD_TYPES),
            'name' => 'required|string',
        ];
    }
}
