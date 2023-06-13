<?php

namespace Ds\Http\Requests;

use Ds\Models\Hook;
use Ds\Models\HookEvent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HookUpdateFormRequest extends FormRequest
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
            'content_type' => [
                'string',
                Rule::in(Hook::CONTENT_TYPES),
                'required_with:active,events,payload_url,secret',
                'required_without:insecure_ssl',
            ],
            'events' => [
                'array',
                'required_with:active,content_type,payload_url,secret',
                'required_without:insecure_ssl',
            ],
            'events.*' => [
                Rule::in(array_merge(
                    array_keys(HookEvent::EVENTS),
                    HookEvent::query()->select('id')->pluck('id')->toArray()
                )),
            ],
            'insecure_ssl' => [
                'boolean',
                'required_without_all:active,content_type,events,payload_url,secret',
            ],
            'payload_url' => [
                'url',
                'required_with:active,content_type,events,secret',
                'required_without:insecure_ssl',
            ],
            'secret' => 'nullable|string',
        ];
    }
}
