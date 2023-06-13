<?php

namespace Ds\Http\Requests\Frontend;

use Ds\Enums\SocialLogin\SupporterProviders;
use Ds\Http\Requests\SocialLoginCallbackRequest;
use Illuminate\Validation\Rule;

class SupporterSocialLoginCallbackRequest extends SocialLoginCallbackRequest
{
    protected $redirectRoute = 'frontend.accounts.login';

    public function authorize(): bool
    {
        return (bool) feature('social_login');
    }

    public function rules(): array
    {
        return [
            'state' => 'required',
            'provider' => ['required', Rule::in(SupporterProviders::cases())],
        ];
    }
}
