<?php

namespace Ds\Http\Requests\API\V2;

use Ds\Http\Requests\Request;

class InventoryStoreFormRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return user()->can(['product.add']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'quantity' => ['required', 'integer'],
        ];
    }
}
