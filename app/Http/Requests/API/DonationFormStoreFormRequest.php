<?php

namespace Ds\Http\Requests\API;

use Ds\Http\Requests\Request;
use Ds\Models\Media;
use Illuminate\Validation\Rule;

class DonationFormStoreFormRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return user()->can('product.add');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'template' => 'in:standard,amount_tiles',
            'layout' => 'in:simplified,standard',
            'transparency_promise_enabled' => 'boolean',
            'transparency_promise_type' => 'required_if:transparency_promise_enabled,true|in:calculation,statement',
            'transparency_promise_1_percentage' => 'required_if:transparency_promise_type,calculation|numeric',
            'transparency_promise_1_description' => 'required_if:transparency_promise_type,calculation|string',
            'transparency_promise_2_percentage' => 'required_if:transparency_promise_type,calculation|numeric',
            'transparency_promise_2_description' => 'required_if:transparency_promise_type,calculation|string',
            'transparency_promise_statement' => 'required_if:transparency_promise_type,statement|string',
            'default_amount_type' => 'required|in:automatic,custom',
            'default_amount_value' => [
                'nullable',
                Rule::requiredIf($this->template !== 'amount_tiles' && $this->default_amount_type === 'custom'),
                'numeric',
                'min:5',
            ],
            'default_amount_values' => [
                'nullable',
                Rule::requiredIf($this->template === 'amount_tiles' && $this->default_amount_type === 'custom'),
                'array',
                'min:5',
                'max:5',
            ],
            'default_amount_values.*' => 'integer|min:5',
            'embed_options_reminder_enabled' => 'boolean',
            'embed_options_reminder_description' => 'required_if:embed_options_reminder_enabled,true',
            'embed_options_reminder_background_colour' => 'required_if:embed_options_reminder_enabled,true|regex:/^#[a-f0-9]{6}$/i',
            'embed_options_reminder_position' => 'required_if:embed_options_reminder_enabled,true|in:bottom_left,bottom_center,bottom_right',
        ];
    }

    public function getDonationFormData(): array
    {
        return [
            'product' => [
                'name' => $this->input('name'),
                'template_suffix' => $this->input('template'),
                'meta1' => $this->input('dp_gl_code'),
                'meta2' => $this->input('dp_solicit_code'),
                'meta3' => $this->input('dp_sub_solicit_code'),
                'meta4' => $this->input('dp_campaign'),
                'meta9' => $this->input('dp_meta_9'),
                'meta10' => $this->input('dp_meta_10'),
                'meta11' => $this->input('dp_meta_11'),
                'meta12' => $this->input('dp_meta_12'),
                'meta13' => $this->input('dp_meta_13'),
                'meta14' => $this->input('dp_meta_14'),
                'meta15' => $this->input('dp_meta_15'),
                'meta16' => $this->input('dp_meta_16'),
                'meta17' => $this->input('dp_meta_17'),
                'meta18' => $this->input('dp_meta_18'),
                'meta19' => $this->input('dp_meta_19'),
                'meta20' => $this->input('dp_meta_20'),
                'meta21' => $this->input('dp_meta_21'),
                'meta22' => $this->input('dp_meta_22'),
                'seo_pagetitle' => $this->input('social_link_title'),
                'seo_pagedescription' => $this->input('social_link_description'),
                'thank_you_email_template' => $this->input('thank_you_email_message'),
            ],
            'metadata' => [
                'donation_forms_layout' => $this->input('layout'),
                'donation_forms_landing_page_headline' => $this->input('landing_page_headline'),
                'donation_forms_landing_page_description' => $this->input('landing_page_description'),
                'donation_forms_branding_logo' => Media::find($this->input('branding_logo')),
                'donation_forms_branding_monthly_logo' => Media::find($this->input('branding_monthly_logo')),
                'donation_forms_branding_colour' => $this->input('branding_colour'),
                'donation_forms_background_image' => Media::find($this->input('background_image')),
                'donation_forms_social_preview_image' => Media::find($this->input('social_preview_image')),
                'donation_forms_billing_periods' => $this->input('billing_periods'),
                'donation_forms_default_amount_type' => $this->input('default_amount_type'),
                'donation_forms_default_amount_value' => $this->input('default_amount_value') ? money($this->input('default_amount_value'))->getAmount() : null,
                'donation_forms_default_amount_values' => $this->input('default_amount_values') ? array_map('intval', $this->input('default_amount_values')) : null,
                'donation_forms_badges_enabled' => (bool) $this->input('badges_enabled'),
                'donation_forms_social_proof_enabled' => (bool) $this->input('social_proof_enabled'),
                'donation_forms_social_proof_privacy' => $this->input('social_proof_privacy'),
                'donation_forms_transparency_promise_enabled' => (bool) $this->input('transparency_promise_enabled'),
                'donation_forms_transparency_promise_type' => $this->input('transparency_promise_type'),
                'donation_forms_transparency_promise_1_percentage' => $this->input('transparency_promise_1_percentage'),
                'donation_forms_transparency_promise_1_description' => $this->input('transparency_promise_1_description'),
                'donation_forms_transparency_promise_2_percentage' => $this->input('transparency_promise_2_percentage'),
                'donation_forms_transparency_promise_2_description' => $this->input('transparency_promise_2_description'),
                'donation_forms_transparency_promise_statement' => $this->input('transparency_promise_statement'),
                'donation_forms_email_optin_description' => $this->input('email_optin_description'),
                'donation_forms_email_optin_nag_message' => $this->input('email_optin_nag_message'),
                'donation_forms_email_optin_enabled' => (bool) $this->input('email_optin_enabled'),
                'donation_forms_upsell_enabled' => (bool) $this->input('upsell_enabled'),
                'donation_forms_upsell_description' => $this->input('upsell_description'),
                'donation_forms_upsell_confirmation' => $this->input('upsell_confirmation'),
                'donation_forms_exit_confirmation_description' => $this->input('exit_confirmation_description'),
                'donation_forms_embed_options_reminder_enabled' => (bool) $this->input('embed_options_reminder_enabled'),
                'donation_forms_embed_options_reminder_description' => $this->input('embed_options_reminder_description'),
                'donation_forms_embed_options_reminder_background_colour' => $this->input('embed_options_reminder_background_colour'),
                'donation_forms_embed_options_reminder_position' => $this->input('embed_options_reminder_position'),
                'donation_forms_double_the_donation_enabled' => $this->input('double_the_donation_enabled'),
                // 'donation_forms_thank_you_peer_to_peer_enabled' => (bool) $this->input('thank_you_peer_to_peer_enabled'),
                'donation_forms_thank_you_onscreen_message' => $this->input('thank_you_onscreen_message'),
                'donation_forms_gtm_container_id' => $this->input('gtm_container_id'),
                'donation_forms_google_ads_pixel_id' => $this->input('google_ads_pixel_id'),
                'donation_forms_meta_pixel_id' => $this->input('meta_pixel_id'),
                'donation_forms_dp_autosync_enabled' => (bool) $this->input('dp_enabled'),
            ],
        ];
    }
}
