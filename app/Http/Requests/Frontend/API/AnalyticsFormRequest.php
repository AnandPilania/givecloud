<?php

namespace Ds\Http\Requests\Frontend\API;

use Ds\Http\Requests\Request;

class AnalyticsFormRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'events.*.visitor_id' => 'required|uuid',
            'events.*.eventable' => 'required|regex:/^product_[ABCDEFGHJKLMNPRSTUVWXYZ23456789]{8,}$/i',
            'events.*.event_name' => 'required|string',
            'events.*.event_category' => 'required|string',
            'events.*.event_value' => 'nullable',
            'events.*.utm_source' => 'nullable|string',
            'events.*.utm_medium' => 'nullable|string',
            'events.*.utm_content' => 'nullable|string',
            'events.*.utm_campaign' => 'nullable|string',
            'events.*.timestamp' => 'required|date',
        ];
    }
}
