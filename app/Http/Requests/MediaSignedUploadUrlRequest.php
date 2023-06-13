<?php

namespace Ds\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

class MediaSignedUploadUrlRequest extends FormRequest
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
            'filename' => 'required|string',
            'collection_name' => 'nullable|in:files,products,sponsorship,sponsorships',
            'content_type' => 'nullable|string',
            'size' => 'nullable|integer',
        ];
    }

    /**
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response(['error' => 'Upload failed.'], Response::HTTP_UNPROCESSABLE_ENTITY));
    }
}
