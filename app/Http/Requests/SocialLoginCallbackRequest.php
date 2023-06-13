<?php

namespace Ds\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SocialLoginCallbackRequest extends FormRequest
{
    protected $redirectRoute = 'backend.session.login';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'state' => 'required',
            'provider' => 'required|in:google,microsoft,facebook',
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->request->has('state')) {
            return;
        }

        $state = json_decode(base64_decode($this->request->get('state')));

        $this->merge([
            'provider' => $state->provider ?? null,
        ]);
    }
}
