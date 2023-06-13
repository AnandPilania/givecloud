<?php

namespace Ds\Http\Requests\Frontend\API\Account;

use Ds\Http\Requests\Request;
use Ds\Illuminate\Validation\Rules\ProfanityRule;

class PeerToPeerFundraisingPageStoreFormRequest extends Request
{
    public function authorize(): bool
    {
        return (bool) member();
    }

    public function rules(): array
    {
        return [
            'title' => [
                'nullable',
                'string',
                'max:200',
                new ProfanityRule,
            ],
            'fundraiser_type' => 'in:team,personal',
            'team_name' => 'required_if:fundraiser_type,team',
            'goal_amount' => 'numeric',
        ];
    }
}
