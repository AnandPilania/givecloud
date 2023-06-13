<?php

namespace Ds\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PledgesInsertFormRequest extends FormRequest
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
            'account_id' => 'required|integer',
            'pledge_campaign_id' => 'required|integer',
        ];
    }
}
