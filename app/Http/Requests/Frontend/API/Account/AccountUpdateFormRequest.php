<?php

namespace Ds\Http\Requests\Frontend\API\Account;

use Ds\Http\Requests\Request;
use Ds\Models\AccountType;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class AccountUpdateFormRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $attributesMap = [
            'account_type_id' => 'account_type_id',
            'email_opt_in' => 'email_opt_in',
            'email_opt_out_reason' => 'email_opt_out_reason',
            'email_opt_out_reason_other' => 'email_opt_out_reason_other',
            'password' => 'password',
            'password_confirmation' => 'password_confirmation',
            'bill_title' => 'billing_title',
            'bill_address_01' => 'billing_address_01',
            'bill_address_02' => 'billing_address_02',
            'bill_city' => 'billing_city',
            'bill_country' => 'billing_country_code',
            // 'bill_email' => 'billing_email',
            'bill_first_name' => 'billing_first_name',
            'bill_last_name' => 'billing_last_name',
            'bill_phone' => 'billing_phone',
            'bill_state' => 'billing_province_code',
            'bill_zip' => 'billing_zip',
            'nps' => 'nps',
            'ship_address_01' => 'shipping_address_01',
            'ship_address_02' => 'shipping_address_02',
            'ship_city' => 'shipping_city',
            'ship_country' => 'shipping_country_code',
            // 'ship_email' => 'shipping_email',
            // 'ship_first_name' => 'shipping_first_name',
            // 'ship_last_name' => 'shipping_last_name',
            // 'ship_phone' => 'shipping_phone',
            'ship_state' => 'shipping_province_code',
            // 'ship_title' => 'shipping_title',
            'ship_zip' => 'shipping_zip',
        ];

        if ($this->canEditNameAndEmail()) {
            $attributesMap += [
                'bill_organization_name' => 'organization_name',
                'email' => 'email',
                'first_name' => 'first_name',
                'last_name' => 'last_name',
                'title' => 'title',
            ];
        }

        $this->replace(array_merge(
            ['id' => member()->getKey()],
            array_map(
                function ($attr) {
                    return $this->{$attr};
                },
                array_filter($attributesMap, function ($attr) {
                    return $this->has($attr);
                }),
            )
        ));
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'account_type_id' => 'exists:account_types,id',
            'bill_organization_name' => Rule::requiredIf(function () {
                return $this->account_type_id
                    && AccountType::find($this->account_type_id)->is_organization;
            }),
            'email_opt_in' => 'boolean|nullable',
            'password' => 'confirmed|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
            'nps' => 'min:1|max:10',
        ];

        if ($this->canEditNameAndEmail()) {
            $rules += [
                'email' => [
                    'email',
                    'required',
                    'sometimes',
                    Rule::unique('member', 'email')
                        ->ignore(member()->getKey()),
                ],
                'first_name' => 'min:1',
                'last_name' => 'min:1',
            ];
        }

        return $rules;
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        $messages = [
            'account_type_id.exists' => __('frontend/api.validation.account_type_not_found'),
            'account_type_id.required' => __('frontend/api.validation.no_account_type_selected'),
            'nps' => __('frontend/api.validation.nps_value_only_from_1_to_10'),
            'password.confirmed' => __('frontend/api.validation.password_confirmation_no_match'),
            'password.min' => __('frontend/api.validation.password_length_8_characters_min'),
            'password.regex' => __('frontend/api.validation.password_at_least_1_uppercase_lowercase_and_number'),
        ];

        if ($this->canEditNameAndEmail()) {
            $messages += [
                'email.unique' => __('frontend/api.email_already_registered'),
            ];
        }

        return $messages;
    }

    public function canEditNameAndEmail(): bool
    {
        return in_array('edit-profile', sys_get('list:account_login_features', []), true);
    }

    public function getValidator(): Validator
    {
        return $this->getValidatorInstance();
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator): void
    {
        // do nothing as it will be "handled" by the controller.
    }
}
