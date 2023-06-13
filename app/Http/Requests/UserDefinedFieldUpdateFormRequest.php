<?php

namespace Ds\Http\Requests;

use Ds\Models\UserDefinedField;
use Illuminate\Foundation\Http\FormRequest;

class UserDefinedFieldUpdateFormRequest extends FormRequest
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
            'entity' => 'nullable|string|in:' . implode(',', UserDefinedField::ENTITIES),
            'field_attributes' => 'nullable|array',
            'field_type' => 'nullable|string|in:' . implode(',', UserDefinedField::FIELD_TYPES),
            'name' => 'nullable|string',
        ];
    }
}
