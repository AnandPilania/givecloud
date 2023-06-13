<?php

namespace Ds\Domain\Zapier\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResthookSubscriptionStoreFormRequest extends FormRequest
{
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
            'event' => 'required|string|regex:/^[a-z]+\.[a-z]+$/', // eg. supporter.create
            'target_url' => 'required|url',
        ];
    }
}
