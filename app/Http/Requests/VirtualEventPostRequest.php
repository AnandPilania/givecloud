<?php

namespace Ds\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VirtualEventPostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return user()->can('virtualevents.edit');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required',
            'logo' => 'required|url',
            'background_image' => 'required|url',
            'theme_style' => 'required',
            'theme_primary_color' => 'required',
            'start_date' => 'required|date',
            'video_source' => 'required|string',
            'is_chat_enabled' => 'required|boolean',
            'is_amount_tally_enabled' => 'required|boolean',
            'chat_id' => 'required_if:video_source,vimeo',
            'is_celebration_enabled' => 'required|boolean',
            'is_honor_roll_enabled' => 'required|boolean',
            'is_emoji_reaction_enabled' => 'required|boolean',
            'celebration_threshold' => 'exclude_if:is_celebration_enabled,false|required|integer',
            'tab_one_label' => 'required_with:tab_one_product_id',
            'tab_one_product_id' => 'required_with:tab_one_label|integer',
            'tab_two_label' => 'required_with:tab_two_product_id',
            'tab_two_product_id' => 'required_with:tab_two_label|integer',
            'tab_three_label' => 'required_with:tab_three_product_id',
            'tab_three_product_id' => 'required_with:tab_three_label|integer',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'tab_one_label.required_with' => 'If you choose a donation item for the first tab, you must enter a label as well.',
            'tab_one_product_id.required_with' => 'If you enter a label for the first tab, you must choose a donation item as well.',
            'tab_two_label.required_with' => 'If you choose a donation item for the second tab, you must enter a label as well.',
            'tab_two_product_id.required_with' => 'If you enter a label for the second tab, you must choose a donation item as well.',
            'tab_three_label.required_with' => 'If you choose a donation item for the third tab, you must enter a label as well.',
            'tab_three_product_id.required_with' => 'If you enter a label for the third tab, you must choose a donation item as well.',
        ];
    }
}
