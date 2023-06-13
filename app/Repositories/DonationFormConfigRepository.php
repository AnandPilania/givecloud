<?php

namespace Ds\Repositories;

use Ds\Domain\Analytics\UserAgent;
use Ds\Domain\Commerce\Currency;
use Ds\Domain\Commerce\Models\PaymentProvider;
use Ds\Http\Resources\DonationForms\DonationFormResource;
use Ds\Http\Resources\PeerToPeer\FundraisingPageResource;
use Ds\Http\Resources\Settings\GlobalSettingsResource;
use Ds\Models\FundraisingPage;
use Ds\Models\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;

class DonationFormConfigRepository
{
    /** @var \Ds\Models\Product */
    private $product;

    /** @var \Ds\Models\FundraisingPage */
    private $fundraisingPage;

    public function setProduct(Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function setFundraisingPage(FundraisingPage $fundraisingPage): self
    {
        $this->fundraisingPage = $fundraisingPage;
        $this->setProduct($fundraisingPage->product);

        return $this;
    }

    public function getConfig(bool $widgetMode = false): array
    {
        if (empty($this->product)) {
            throw new ModelNotFoundException;
        }

        $donationForm = DonationFormResource::make($this->product)->toObject();

        $provider = PaymentProvider::getCreditCardProvider(false);
        $provider ??= PaymentProvider::getPayPalProvider(false);

        return [
            'id' => $this->product->code,
            'livemode' => ! $provider->test_mode,
            'layout' => $this->getWidgetType($widgetMode) === 'inline_embed' ? 'simplified' : $donationForm->layout,
            'template' => $donationForm->template,
            'widget_type' => $this->getWidgetType($widgetMode),
            'asset_url' => secure_site_url(app_asset_url('assets/apps/donation-forms', false)),
            'logo_url' => $donationForm->branding_logo->thumb ?? null,
            'monthly_logo_url' => $donationForm->branding_monthly_logo->thumb ?? null,
            'page_title' => $donationForm->social_link_title,
            'page_description' => $donationForm->social_link_description,
            'landing_page_headline' => $donationForm->landing_page_headline,
            'landing_page_description' => $donationForm->landing_page_description,
            'background_url' => $donationForm->background_image->full ?? null,
            'social_preview_image' => $donationForm->social_preview_image->custom->social_preview ?? jpanel_asset_url('images/default-preview.png'),
            'primary_colour' => $donationForm->branding_colour,
            'enable_sound' => false,
            'email_optin_enabled' => $donationForm->email_optin_enabled,
            'email_optin_description' => $donationForm->email_optin_description,
            'email_optin_nag_message' => $donationForm->email_optin_nag_message,
            'thank_you_onscreen_message' => $donationForm->thank_you_onscreen_message,
            'thank_you_onscreen_monthly_message' => $donationForm->thank_you_onscreen_monthly_message,
            'campaign_url' => $donationForm->public_url,
            'accounts_login_url' => route('frontend.accounts.login'),
            'local_country' => app('iso3166')->countryForIp(),
            'local_currency' => currency($this->fundraisingPage->currency_code ?? Currency::getBestLocalCurrencyForIp())->toArray(),
            'local_geolocation' => $this->getLocalGeolocation(),
            'eventable' => "product_{$this->product->hashid}",
            'variants' => $this->getVariants($donationForm),
            'default_amount' => $this->getDefaultAmount($donationForm),
            // 'preset_amounts' => Str::of($this->product->defaultVariant->price_presets)->explode(',')->map(fn ($value): float => $value),
            'preset_amounts' => [5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 75, 100, 125, 150, 200, 250, 500, 750, 1000, 1500, 2000, 2500, 5000, 7500, 10000, 12500, 15000, 17500, 20000, 22500, 25000, 50000],
            'default_amounts' => $donationForm->default_amount_type === 'custom' ? $donationForm->default_amount_values : $this->getAutomaticDefaultAmounts(),
            'floating_icons' => [
                'amount_stepper' => [
                    'prev' => false,
                    'next' => true,
                ],
                'variant_button' => [
                    'onetime' => false,
                    'monthly' => true,
                ],
            ],
            'cover_costs' => [
                'enabled' => sys_get('bool:dcc_enabled'),
                'default_type' => sys_get('dcc_ai_is_enabled') && $donationForm->cover_costs_default_type !== 'none' ? $donationForm->cover_costs_default_type : null,
                'using_ai' => sys_get('bool:dcc_ai_is_enabled'),
            ],
            'transparency_promise' => [
                'enabled' => $donationForm->transparency_promise_enabled,
                'promises' => collect([
                    ['percentage' => $donationForm->transparency_promise_1_percentage, 'label' => $donationForm->transparency_promise_1_description],
                    ['percentage' => $donationForm->transparency_promise_2_percentage, 'label' => $donationForm->transparency_promise_2_description],
                ])->reject(fn ($promise) => empty($promise['label']))->values(),
                'statement' => $donationForm->transparency_promise_statement,
                'type' => $donationForm->transparency_promise_type,
            ],
            'navigation' => [
                'footer_cta' => [
                    'enabled' => (bool) $donationForm->navigation_footer_cta_enabled,
                    'label' => $donationForm->navigation_footer_cta_label,
                    'link' => $donationForm->navigation_footer_cta_link,
                ],
            ],
            'fundraising_page_id' => $this->fundraisingPage->id ?? null,
            'fundraising_member_id' => $this->fundraisingPage->member_organizer_id ?? null,
            'global_settings' => GlobalSettingsResource::make(),
            'payment_provider_website_url' => optional($provider)->getWebsiteUrl(),
            'require_billing_address' => (bool) $donationForm->require_billing_address,
            'badges' => [
                'enabled' => true, // $donationForm->badges_enabled,
            ],
            'upsell' => [
                'enabled' => (bool) $donationForm->upsell_enabled,
                'heading' => $donationForm->upsell_heading,
                'description' => $donationForm->upsell_description,
                'confirmation' => $donationForm->upsell_confirmation,
            ],
            'double_the_donation' => [
                'enabled' => sys_get('double_the_donation_enabled') && $donationForm->double_the_donation_enabled,
                'publishable_key' => sys_get('double_the_donation_public_key') ?: null,
            ],
            'peer_to_peer' => [
                'enabled' => $donationForm->thank_you_peer_to_peer_enabled,
                'campaign' => $this->getPeerToPeerCampaign(),
                'redirect_to' => $donationForm->thank_you_peer_to_peer_enabled ? route('peer-to-peer-campaign.spa', ['code' => $this->product->code]) : null,
            ],
            'social_proof' => [
                'enabled' => $donationForm->social_proof_enabled,
                'privacy_mode' => $donationForm->social_proof_privacy,
                'proofs' => app(SocialProofRepository::class)->get($this->product),
            ],
            'exit_confirmation' => [
                'enabled' => true,
                'description' => $donationForm->exit_confirmation_description,
            ],
            'gtm_container_id' => $donationForm->gtm_container_id,
            'meta_pixel_id' => $donationForm->meta_pixel_id,
            'embed_options' => [
                'reminder' => [
                    'enabled' => (bool) $donationForm->embed_options_reminder_enabled,
                    'description' => $donationForm->embed_options_reminder_description,
                    'background_colour' => $donationForm->embed_options_reminder_background_colour,
                    'position' => $donationForm->embed_options_reminder_position,
                ],
            ],
        ];
    }

    private function getDefaultAmount(object $donationForm): int
    {
        if ($donationForm->template !== 'amount_tiles') {
            return $this->getStandardDefaultAmount($donationForm);
        }

        if ($donationForm->default_amount_type === 'custom') {
            return data_get($donationForm->default_amount_values, '1');
        }

        return data_get($this->getAutomaticDefaultAmounts(), '1');
    }

    private function getStandardDefaultAmount(object $donationForm): int
    {
        $userAgent = UserAgent::make();

        if ($donationForm->default_amount_type === 'custom') {
            return $donationForm->default_amount_value;
        }

        if ($userAgent->isLatestAndroidOS()) {
            return 75;
        }

        if ($userAgent->isLatestIOS() || $userAgent->isLatestOSX()) {
            return 150;
        }

        return 45;
    }

    private function getAutomaticDefaultAmounts(): array
    {
        $userAgent = UserAgent::make();

        if ($userAgent->isLatestAndroidOS()) {
            return [45, 95, 145, 250, 500];
        }

        if ($userAgent->isLatestIOS() || $userAgent->isLatestOSX()) {
            return [65, 145, 250, 500, 1500];
        }

        return [25, 45, 95, 145, 245];
    }

    private function getLocalGeolocation(): ?array
    {
        $locationData = rescue(fn () => app('geoip')->getLocationData(), null, false);

        return empty($locationData->lat) ? null : [$locationData->lat, $locationData->lon];
    }

    private function getVariants(object $donationForm): array
    {
        $payPalProvider = PaymentProvider::getPayPalProvider(false);
        $creditCardProvider = PaymentProvider::getCreditCardProvider(false);

        $supportForRecurring = $creditCardProvider || optional($payPalProvider)->config('reference_transactions');

        $variants = $this->product->variants
            ->filter(function ($variant) use ($supportForRecurring) {
                return $variant->billing_period === 'onetime' || $supportForRecurring;
            })->filter(function ($variant) use ($donationForm) {
                $usingVariants = [
                    'onetime' => fn () => Str::contains($donationForm->billing_periods, 'today_only'),
                    'monthly' => fn () => Str::contains($donationForm->billing_periods, 'monthly'),
                ];

                return (bool) value($usingVariants[$variant->billing_period] ?? false);
            })->map(function ($variant) use ($donationForm) {
                $defaultVariantMapping = [
                    'onetime' => fn () => Str::startsWith($donationForm->billing_periods, 'today_only'),
                    'monthly' => fn () => Str::startsWith($donationForm->billing_periods, 'monthly'),
                ];

                return [
                    'id' => $variant->getKey(),
                    'title' => $variant->variantname,
                    'is_default' => (bool) value($defaultVariantMapping[$variant->billing_period] ?? false),
                    'billing_period' => $variant->billing_period,
                ];
            })->values()
            ->all();

        if (count($variants) === 1) {
            $variants[0]['is_default'] = true;
        }

        return $variants;
    }

    private function getPeerToPeerCampaign(): ?array
    {
        if (empty($this->fundraisingPage)) {
            return null;
        }

        $fundraisingPage = FundraisingPageResource::make($this->fundraisingPage)->toObject();

        return [
            'title' => $fundraisingPage->title,
            'team_name' => $fundraisingPage->team_name,
            'team_goal_amount' => $fundraisingPage->team_goal_amount,
            'team_currency_code' => $fundraisingPage->team_currency_code,
            'goal_amount' => $fundraisingPage->goal_amount,
            'currency_code' => $fundraisingPage->currency_code,
            'avatar_name' => $fundraisingPage->avatar_name,
            'social_avatar' => $fundraisingPage->social_avatar,
            'amount_raised' => $fundraisingPage->amount_raised,
        ];
    }

    private function getWidgetType(bool $widgetMode): string
    {
        if ($widgetMode) {
            $widgetTypes = [
                'inline_embed',
                'modal_embed',
            ];

            $widgetType = request('widget_type', 'modal_embed');

            return in_array($widgetType, $widgetTypes, true) ? $widgetType : 'modal_embed';
        }

        return 'hosted_page';
    }
}
