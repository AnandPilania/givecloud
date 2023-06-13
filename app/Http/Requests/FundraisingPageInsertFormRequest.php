<?php

namespace Ds\Http\Requests;

use Ds\Domain\Commerce\Currency;
use Ds\Illuminate\Validation\Rules\ProfanityRule;
use Ds\Services\SupporterVerificationStatusService;
use Illuminate\Validation\Rule;

class FundraisingPageInsertFormRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(SupporterVerificationStatusService $supporterVerificationStatusService)
    {
        return $supporterVerificationStatusService->supporterIsNotDenied(member());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'page_type_id' => 'required|integer',
            'page_name' => [
                'required',
                'string',
                new ProfanityRule,
            ],
            'category' => 'string',
            'content' => [
                'string',
                new ProfanityRule,
            ],
            'currency_code' => 'string|in:' . implode(',', array_keys(Currency::getCurrencies())),
            'goal_deadline' => 'nullable|date',
            'goal_amount' => 'numeric',
            'is_team' => 'nullable|boolean',
            'page_photo' => 'required',
            'page_photo_custom' => [
                Rule::requiredIf(function () {
                    return request('page_photo') === 'custom' && empty(request('custom_image_path'));
                }),
                'image',
            ],
            'team_name' => 'required_if:is_team,true|sometimes|string',
            'video' => 'nullable|url',
        ];
    }
}
