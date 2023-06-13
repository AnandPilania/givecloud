<?php

namespace Ds\Http\Requests\API;

class DonationFormUpdateFormRequest extends DonationFormStoreFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return user()->can('product.edit');
    }
}
