<?php

namespace Ds\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CommentUpdateFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $comment = $this->route('comment');

        return $comment && $comment->userCanOrRedirect(['member.add']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'body' => 'required',
        ];
    }
}
