<?php

namespace Ds\Http\Resources\DonationForms;

use Ds\Domain\Analytics\Models\AnalyticsEvent;
use Ds\Http\Resources\MediaResource;
use Ds\Illuminate\Http\Resources\Json\JsonResource;
use Ds\Models\Order;
use Ds\Services\DonationFormStatsService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use SocialLinks\Page as SocialLinksPage;

/** @mixin \Ds\Models\Product */
class DonationFormResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->hashid,
            'name' => $this->name ?: null,
            'template' => $this->template_suffix ?: 'standard',
            'layout' => $this->metadata['donation_forms_layout'] ?: $this->getDefaultLayout(),
            'is_default_form' => (bool) $this->metadata['donation_forms_is_default_form'],
            'branding_logo' => $this->getBrandingLogo(),
            'branding_monthly_logo' => $this->getBrandingMonthlyLogo(),
            'branding_colour' => $this->metadata['donation_forms_branding_colour'],
            'landing_page_headline' => $this->metadata['donation_forms_landing_page_headline'] ?: 'Donate Today',
            'landing_page_description' => $this->metadata['donation_forms_landing_page_description'] ?: 'Join countless others in creating a meaningful impact.',
            'background_image' => $this->getBackgroundImage(),
            'social_link_title' => $this->seo_pagetitle ?: null,
            'social_link_description' => $this->seo_pagedescription ?: null,
            'social_preview_image' => $this->getSocialPreviewImage(),
            'billing_periods' => $this->metadata['donation_forms_billing_periods'] ?? 'monthly|today_only',
            'default_amount_type' => $this->metadata['donation_forms_default_amount_type'],
            'default_amount_value' => $this->metadata['donation_forms_default_amount_value'],
            'default_amount_values' => $this->metadata['donation_forms_default_amount_values'],
            'badges_enabled' => (bool) $this->metadata['donation_forms_badges_enabled'],
            'social_proof_enabled' => (bool) $this->metadata['donation_forms_social_proof_enabled'],
            'social_proof_privacy' => $this->metadata['donation_forms_social_proof_privacy'],
            'transparency_promise_enabled' => (bool) $this->metadata['donation_forms_transparency_promise_enabled'],
            'transparency_promise_type' => $this->metadata['donation_forms_transparency_promise_type'] ?? 'calculation',
            'transparency_promise_1_percentage' => nullable_cast('int', $this->metadata['donation_forms_transparency_promise_1_percentage']),
            'transparency_promise_1_description' => $this->metadata['donation_forms_transparency_promise_1_description'],
            'transparency_promise_2_percentage' => nullable_cast('int', $this->metadata['donation_forms_transparency_promise_2_percentage']),
            'transparency_promise_2_description' => $this->metadata['donation_forms_transparency_promise_2_description'],
            'transparency_promise_statement' => $this->metadata['donation_forms_transparency_promise_statement'],
            'email_optin_description' => $this->metadata['donation_forms_email_optin_description'],
            'email_optin_nag_message' => $this->metadata['donation_forms_email_optin_nag_message'],
            'email_optin_enabled' => $this->metadata['donation_forms_email_optin_enabled'] ?? true,
            'upsell_enabled' => (bool) $this->metadata['donation_forms_upsell_enabled'],
            'upsell_heading' => $this->metadata['donation_forms_upsell_heading'],
            'upsell_description' => $this->metadata['donation_forms_upsell_description'],
            'upsell_confirmation' => $this->metadata['donation_forms_upsell_confirmation'],
            'double_the_donation_connected' => (bool) sys_get('double_the_donation_enabled'),
            'double_the_donation_enabled' => (bool) $this->metadata['donation_forms_double_the_donation_enabled'],
            'thank_you_peer_to_peer_enabled' => feature('fundraising_forms_peer_to_peer') && $this->metadata['donation_forms_thank_you_peer_to_peer_enabled'],
            'thank_you_onscreen_message' => $this->metadata['donation_forms_thank_you_onscreen_message'],
            'thank_you_onscreen_monthly_message' => $this->metadata['donation_forms_thank_you_onscreen_monthly_message'],
            'thank_you_email_message' => $this->thank_you_email_template ?: null,
            'thank_you_email_monthly_message' => $this->metadata['donation_forms_thank_you_email_monthly_message'],
            'navigation_footer_cta_enabled' => (bool) $this->metadata['donation_forms_navigation_footer_cta_enabled'],
            'navigation_footer_cta_label' => $this->metadata['donation_forms_navigation_footer_cta_label'],
            'navigation_footer_cta_link' => $this->metadata['donation_forms_navigation_footer_cta_link'],
            'exit_confirmation_description' => $this->metadata['donation_forms_exit_confirmation_description'] ?: 'Are you sure you want to leave without making a difference?',
            'require_billing_address' => $this->metadata['donation_forms_require_billing_address'] ?? true,
            'cover_costs_default_type' => $this->metadata['donation_forms_cover_costs_default_type'] ?? 'more_costs',
            'gtm_container_id' => $this->metadata['donation_forms_gtm_container_id'],
            'google_ads_pixel_id' => $this->metadata['donation_forms_google_ads_pixel_id'],
            'meta_pixel_id' => $this->metadata['donation_forms_meta_pixel_id'],
            'dp_enabled' => (bool) $this->metadata['donation_forms_dp_autosync_enabled'],
            'dp_gl_code' => $this->meta1,
            'dp_campaign' => $this->meta2,
            'dp_solicit_code' => $this->meta3,
            'dp_sub_solicit_code' => $this->meta4,
            'dp_meta_9' => $this->meta9,
            'dp_meta_10' => $this->meta10,
            'dp_meta_11' => $this->meta11,
            'dp_meta_12' => $this->meta12,
            'dp_meta_13' => $this->meta13,
            'dp_meta_14' => $this->meta14,
            'dp_meta_15' => $this->meta15,
            'dp_meta_16' => $this->meta16,
            'dp_meta_17' => $this->meta17,
            'dp_meta_18' => $this->meta18,
            'dp_meta_19' => $this->meta19,
            'dp_meta_20' => $this->meta20,
            'dp_meta_21' => $this->meta21,
            'dp_meta_22' => $this->meta22,
            'embed_options_reminder_enabled' => (bool) $this->metadata['donation_forms_embed_options_reminder_enabled'],
            'embed_options_reminder_description' => $this->metadata['donation_forms_embed_options_reminder_description'] ?: "We're counting on your support!",
            'embed_options_reminder_background_colour' => $this->metadata['donation_forms_embed_options_reminder_background_colour'],
            'embed_options_reminder_position' => $this->metadata['donation_forms_embed_options_reminder_position'] ?? 'bottom_right',
            'is_tax_receiptable' => (bool) $this->is_tax_receiptable,
            'public_url' => $this->abs_url,
            'shortlink_url' => shortlink($this->abs_url, $this->resource),
            'preview_image_url' => $this->getPreviewImageUrl(),
            'testmode_url' => $this->getTestmodeUrl(),
            'stats' => $this->when($request->input('include_stats'), fn () => $this->getStats()),
            'qr_code' => route('donation-forms.qr-code', $this->hashid),
            'created_by' => $this->getCreatedBy(),
            'created_at' => toUtcFormat($this->createddatetime, 'api'),
            'updated_at' => toUtcFormat($this->modifieddatetime, 'api'),
        ];
    }

    private function getDefaultLayout(): string
    {
        if (optional($this->createddatetime)->lte(fromUtc('2023-02-06 15:00:00'))) {
            return 'simplified';
        }

        return 'standard';
    }

    private function getCreatedBy(): string
    {
        $firstName = Str::firstName($this->createdBy->name);
        $lastName = mb_substr(Str::lastName($this->createdBy->name), 0, 1, 'UTF-8');

        return $firstName . ' ' . $lastName . '.';
    }

    private function getBrandingLogo(): ?MediaResource
    {
        $media = rescueQuietly(fn () => $this->metadata['donation_forms_branding_logo']);

        return $media ? MediaResource::make($media)->setThumb(['400x', 'crop' => 'entropy', 'trim' => true]) : null;
    }

    private function getBrandingMonthlyLogo(): ?MediaResource
    {
        $media = rescueQuietly(fn () => $this->metadata['donation_forms_branding_monthly_logo']);

        return $media ? MediaResource::make($media)->setThumb(['400x', 'crop' => 'entropy', 'trim' => true]) : null;
    }

    private function getBackgroundImage(): ?MediaResource
    {
        $media = rescueQuietly(fn () => $this->metadata['donation_forms_background_image']);

        return $media ? MediaResource::make($media) : null;
    }

    private function getSocialPreviewImage(): ?MediaResource
    {
        $media = rescueQuietly(fn () => $this->metadata['donation_forms_social_preview_image']);

        return $media
            ? MediaResource::make($media)->setCustom(['social_preview' => ['1200x1200', 'crop' => 'entropy']])
            : null;
    }

    public function getShareLinks(): array
    {
        $page = new SocialLinksPage([
            'url' => $this->abs_url,
            'title' => strip_tags($this->seo_pagetitle),
            'text' => strip_tags($this->seo_pagedescription),
            'image' => optional($this->getSocialPreviewImage())->toObject()->custom->social_preview ?? null,
        ]);

        return [
            'facebook' => $page->facebook->shareUrl,
            'twitter' => $page->twitter->shareUrl,
            'linkedin' => $page->linkedin->shareUrl,
            'sms' => $page->sms->shareUrl,
            'email' => $page->email->shareUrl,
        ];
    }

    private function getPreviewImageUrl(): ?string
    {
        if (isDev() || ! $this->exists) {
            return jpanel_asset_url('images/fundraise-donation-screen.png');
        }

        return sprintf(
            'https://cdn.givecloud.co/forms/%s/%s?v=%s',
            site()->secure_domain,
            urlencode($this->code),
            $this->getContentDigest(),
        );
    }

    private function getTestmodeUrl(): ?string
    {
        if (user()->can('product.view')) {
            return $this->abs_url . '?testmode_token=' . user()->getTestmodeToken();
        }

        return null;
    }

    private function getStats(): array
    {
        $orders = Order::query()
            ->paid()
            ->notFullyRefunded()
            ->where('ordered_at', '>', $this->createddatetime)
            ->whereHas('items', fn ($query) => $query->whereIn('productinventoryid', $this->variants()->pluck('id')))
            ->select([
                DB::raw('COUNT(DISTINCT productorder.id) as order_count'),
                DB::raw('SUM(productorder.functional_total - (IFNULL(productorder.refunded_amt, 0) * productorder.functional_exchange_rate)) as revenue_amount'),
                DB::raw('COUNT(distinct member_id) as donor_count'),
            ])->first();

        $visits = AnalyticsEvent::query()
            ->join('analytics_visits', 'analytics_visits.id', 'analytics_events.analytics_visit_id')
            ->select('visitor_id')
            ->where(function (Builder $query) {
                $query->where(fn (Builder $query) => $query->where('event_category', 'fundraising_forms.modal_embed')->where('event_name', 'open'))
                    ->orWhere(fn (Builder $query) => $query->where('event_category', 'fundraising_forms.hosted_page')->where('event_name', 'pageview'))
                    ->orWhere(fn (Builder $query) => $query->where('event_category', 'fundraising_forms.inline_embed')->where('event_name', 'impression'));
            })->where('eventable_type', 'product')
            ->where('eventable_id', $this->id)
            ->distinct()
            ->count('visitor_id');

        $contributions = AnalyticsEvent::query()
            ->select(DB::raw('COUNT(id) as total'))
            ->where('event_name', 'contribution_paid')
            ->where('event_category', 'fundraising_forms')
            ->where('eventable_type', 'product')
            ->where('eventable_id', $this->id)
            ->count();

        return [
            'donor_count' => (int) $orders->donor_count,
            'revenue_amount' => (float) $orders->revenue_amount,
            'conversion' => $visits > 0 ? $contributions / $visits * 100 : -1,
            'currency' => currency()->getCode(),
            'views' => $visits,
            'trends' => $this->when(request()->input('include_trends'), fn () => app(DonationFormStatsService::class)->all($this->resource)),
        ];
    }
}
