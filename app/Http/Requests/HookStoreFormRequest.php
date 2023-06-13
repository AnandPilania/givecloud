<?php

namespace Ds\Http\Requests;

use Ds\Models\Hook;
use Ds\Models\HookEvent;
use Illuminate\Foundation\Http\FormRequest;

class HookStoreFormRequest extends FormRequest
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
            'active' => 'sometimes|boolean',
            'content_type' => 'required|in:' . implode(',', Hook::CONTENT_TYPES),
            'events' => 'required|array',
            'events.*' => 'required|in:' . HookEvent::getEnabledEvents()->implode(','),
            'payload_url' => 'required|url',
            'secret' => 'nullable|string',
        ];
    }
}
