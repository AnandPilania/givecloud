<?php

namespace Ds\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SponsorshipSaveFormRequest extends FormRequest
{
    use FlashFailedValidationTrait;

    protected $failedValidationDefaultFlashMessage = 'An error occurred while saving the sponsorship.';

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
            'birth_date' => 'date|nullable',
        ];
    }
}
