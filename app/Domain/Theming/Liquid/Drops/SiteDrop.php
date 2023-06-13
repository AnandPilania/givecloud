<?php

namespace Ds\Domain\Theming\Liquid\Drops;

use Ds\Domain\Commerce\Currency;
use Ds\Domain\Theming\Liquid\Drop;
use Ds\Services\SocialLinkService;

class SiteDrop extends Drop
{
    const SOURCE_REQUIRED = false;

    protected function initialize($source)
    {
        $this->liquid = [
            'secure_url' => secure_site_url('/'),
            'show_branding' => site()->isTrial(),
        ];
    }

    /**
     * Catch all method that is invoked before a specific method
     *
     * @param string $method
     * @return mixed
     */
    protected function liquidMethodMissing($method)
    {
        $keys = [
            'alternate_color' => 'default_color_3',
            'domain' => 'ds_account_name',
            'donor_title' => 'donor_title',
            'google_property_id' => 'webStatsPropertyId',
            'logo' => 'default_logo',
            'name' => 'clientName',
            'primary_color' => 'default_color_1',
            'secondary_color' => 'default_color_2',
            'default_country' => 'default_country',
            'pinned_countries' => 'list:pinned_countries',
            'subscription_default_type' => 'rpp_default_type',
            'captcha_type' => 'captcha_type',
            'cover_costs_enabled' => 'dcc_enabled',
            'cover_costs_ai_enabled' => 'dcc_ai_is_enabled',
            'cover_costs_cost_per_order' => 'dcc_cost_per_order',
            'cover_costs_percentage' => 'dcc_percentage',
            'cover_costs_invoice_label' => 'dcc_invoice_label',
            'ecomm_wallet_pay' => 'bool:feature_ecomm_wallet_pay',
            'sponsorship_maturity_age' => 'int:sponsorship_maturity_age',
            'fundraising_pages_require_guideline_acceptance' => 'fundraising_pages_require_guideline_acceptance',
            'fundraising_pages_guidelines' => 'fundraising_pages_guidelines',
            'preserve_amount_on_variant_change' => 'preserve_amount_on_variant_change',
            'embeddable_donate_custom_snippet' => 'embeddable_donate_custom_snippet',
        ];

        if (array_key_exists($method, $keys)) {
            return sys_get($keys[$method]);
        }

        return parent::liquidMethodMissing($method);
    }

    public function cover_costs_checkout_label()
    {
        if (sys_get('dcc_ai_is_enabled')) {
            return __('frontend/theme.dcc.checkout_label');
        }

        return sys_get('dcc_checkout_label');
    }

    public function cover_costs_checkout_description()
    {
        if (sys_get('dcc_ai_is_enabled')) {
            return __('frontend/theme.dcc.checkout_description');
        }

        return sys_get('dcc_checkout_description');
    }

    public function cover_costs_checkout_description_with_amount()
    {
        if (sys_get('dcc_ai_is_enabled')) {
            return __('frontend/theme.dcc.checkout_description');
        }

        return sys_get('dcc_checkout_description_with_amount');
    }

    public function csrf_token()
    {
        return csrf_token();
    }

    public function force_country()
    {
        if (sys_get('bool:force_country')) {
            return sys_get('default_country');
        }
    }

    public function gift_aid()
    {
        return sys_get('bool:gift_aid');
    }

    public function host()
    {
        return site('secure_domain');
    }

    public function currency()
    {
        return currency()->code;
    }

    public function currency_symbol()
    {
        return currency()->symbol;
    }

    public function money_with_currency()
    {
        if (sys_get('bool:money_with_currency_preference')) {
            return true;
        }

        return count($this->currencies()) > 1;
    }

    public function currencies()
    {
        return collect(Currency::getLocalCurrencies())->map(function ($currency) {
            return $currency->toLiquid();
        })->values();
    }

    public function account_features()
    {
        $features = sys_get('list:account_login_features');

        if (! sys_get('allow_member_to_end_sponsorship')) {
            $features = collect($features)
                ->reject(fn ($feature) => $feature === 'end-sponsorships')
                ->values()
                ->toArray();
        }

        if (in_array('view-recurring', $features)) {
            $features[] = 'view-subscriptions';
        }

        return $features;
    }

    public function donor_title_options()
    {
        return sys_get('list:donor_title_options');
    }

    public function fundraising_page_categories()
    {
        return sys_get('list:fundraising_pages_categories');
    }

    public function locale()
    {
        $locale = app()->getLocale();

        return [
            'iso' => $locale,
            'language' => locale_get_primary_language($locale),
            'region' => locale_get_region($locale),
        ];
    }

    public function payment_day_options()
    {
        return sys_get('list:payment_day_options');
    }

    public function payment_weekday_options()
    {
        $weekdays = [];
        foreach (sys_get('list:payment_day_of_week_options') as $day) {
            $weekdays[$day] = day_of_week($day);
        }

        return $weekdays;
    }

    public function pusher_config()
    {
        if (config('broadcasting.default') === 'ably') {
            return [
                'key' => config('broadcasting.connections.ably.subscribe_only_key'),
                'httpHost' => 'realtime-pusher.ably.io',
                'wsHost' => 'realtime-pusher.ably.io',
                'disableStats' => true,
                'encrypted' => true,
            ];
        }

        return ['key' => config('broadcasting.connections.pusher.key')];
    }

    public function recaptcha_site_key()
    {
        if (sys_get('captcha_type') === 'hcaptcha') {
            return config('services.hcaptcha.site_key');
        }

        return config('services.recaptcha.site_key');
    }

    public function referral_sources()
    {
        return [
            'enabled' => sys_get('bool:referral_sources_isactive'),
            'sources' => sys_get('list:referral_sources_options'),
            'allow_other' => sys_get('referral_sources_other'),
        ];
    }

    public function nps()
    {
        return [
            'enabled' => sys_get('bool:nps_enabled'),
        ];
    }

    public function social_login_is_enabled(): bool
    {
        return (bool) feature('social_login');
    }

    public function social_urls()
    {
        $share_link = (member()) ? member()->getShareableLink('/') : secure_site_url('/');

        return SocialLinkService::generate($share_link, sys_get('clientName'));
    }

    public function subscription_cancel_reasons()
    {
        return [
            'reasons' => sys_get('list:rpp_cancel_reasons'),
            'allow_other' => sys_get('bool:rpp_cancel_allow_other_reason'),
        ];
    }

    public function sponsorship_end_reasons()
    {
        return sys_get('list:public_sponsorship_end_reasons');
    }

    public function recurring_day_options()
    {
        return sys_get('list:payment_day_options');
    }

    public function recurring_day_of_week_options()
    {
        return sys_get('list:payment_day_of_week_options');
    }

    public function partner()
    {
        $partner = site()->partner;

        if (empty($partner)) {
            return null;
        }

        return [
            'identifier' => $partner->identifier,
            'name' => $partner->name,
            'brand' => $partner->in_app_brand,
            'brand_phrase' => $partner->in_app_brand_phrase,
            'brand_url' => $partner->in_app_brand_phrase_url,
            'brand_img' => $partner->in_app_logo_light ?? $partner->in_app_logo,
        ];
    }

    public function synonyms()
    {
        return [
            'order' => sys_get('syn_order'),
            'orders' => sys_get('syn_orders'),
            'cart_heading' => sys_get('syn_cart_heading'),
            'cart_view_label' => sys_get('syn_cart_view_label'),
            'cart_checkout_label' => sys_get('syn_cart_checkout_label'),
            'checkout_my_cart_label' => sys_get('syn_checkout_my_cart_label'),
            'checkout_checkout_label' => sys_get('syn_checkout_checkout_label'),
            'checkout_billing_label' => sys_get('syn_checkout_billing_label'),
            'checkout_shipping_label' => sys_get('syn_checkout_shipping_label'),
            'checkout_complete' => sys_get('syn_checkout_complete'),
            'sponsorship_child' => sys_get('syn_sponsorship_child'),
            'sponsorship_children' => sys_get('syn_sponsorship_children'),
            'group' => sys_get('syn_group'),
            'groups' => sys_get('syn_groups'),
            'group_member' => sys_get('syn_group_member'),
            'group_members' => sys_get('syn_group_members'),
        ];
    }

    public function url(): string
    {
        return secure_site_url();
    }
}
