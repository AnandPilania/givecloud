<?php

namespace Ds\Repositories;

use Ds\Domain\Commerce\Models\PaymentProvider;
use Ds\Domain\MissionControl\MissionControlService;
use Ds\Domain\QuickStart\QuickStartService;
use Ds\Http\Resources\CurrencyResource;

class AdminSpaConfigRepository
{
    public function get(string $appSource): array
    {
        return [
            'accountName' => sys_get('ds_account_name'),
            'cannyBoardToken' => config('services.canny.board_token'),
            'canUserLiveChat' => (bool) user()->can_live_chat,
            'canUserViewAdmin' => (bool) user()->can('admin'),
            'canUserViewBilling' => (bool) user()->can('admin.billing'),
            'clientMissionControlUrl' => MissionControlService::getMissionControlUrl() . '/clients/' . site()->client_id,
            'clientName' => sys_get('clientName'),
            'clientUrl' => secure_site_url(),
            'currency' => CurrencyResource::make(currency())->resolve(),
            'donorPerfectConfig' => $this->getDonorPerfectConfig(),
            'enableLogRocket' => ! app()->environment('local') && ! is_super_user() && sys_get('enable_admin_logrocket'),
            'fundraiseEarlyAccessStatus' => (bool) sys_get('fundraise_early_access_requested'),
            'hasOutstandingInvoice' => app(ChargebeeRepository::class)->hasPastDueBalance(),
            'initialAppSource' => $appSource,
            'isDevelopment' => (bool) site()->client->is_development,
            'isDonorPerfectEnabled' => (bool) dpo_is_enabled(),
            'isFundraisingFormsEnabled' => (bool) feature('fundraising_forms'),
            'isFundraisingFormsStandardLayoutEnabled' => (bool) feature('fundraising_forms_standard_layout'),
            'isGivecloudExpress' => isGivecloudExpress(),
            'isGivecloudPro' => ! isGivecloudExpress(),
            'isMissingPaymentMethod' => (bool) ! app(ChargebeeRepository::class)->hasValidPaymentSource(),
            'isNpsEnabled' => (bool) sys_get('nps_enabled'),
            'isReferralSourcesEnabled' => (bool) sys_get('referral_sources_isactive'),
            'isSuperUser' => (bool) is_super_user(),
            'isSupporterSearchEnabled' => feature('supporter_search') && user()->can('member'),
            'isTestMode' => (bool) data_get(PaymentProvider::getCreditCardProvider(false), 'test_mode', true),
            'isTrial' => (bool) site()->isTrial(),
            'localTime' => now()->toLocal()->untranslatedFormat('g:iA T'),
            'pinnedMenuItems' => app(AdminSidebarMenuRepository::class)->asPinnedItems(),
            'pusherConfig' => $this->pusherConfig(),
            'orgLegalNumber' => sys_get('org_legal_number') ?: null,
            'shouldShowExpandedChecklist' => app(QuickStartService::class)->shouldShowExpandedChecklist(),
            'siteSubscriptionSupportDirectLine' => site()->subscription->support_direct_line,
            'siteSubscriptionSupportPhone' => site()->subscription->support_phone,
            'sponsorshipLabel' => sys_get('syn_sponsorship_children'),
            'timezone' => sys_get('timezone'),
            'trialDaysRemaining' => site()->isTrial() ? (int) site()->getDaysRemainingInTrial() : null,
            'uiFeaturePreviewMenuItems' => app(AdminSidebarMenuRepository::class)->get(),
            'updates' => app(MissionControlService::class)->getInAppUpdates(),
            'userEmail' => user('email'),
            'userFirstName' => user('firstname'),
            'userFullName' => user('full_name'),
            'userShowFundraisingPixelInstructions' => user()->metadata('show_fundraising_pixel_instructions', true),
        ];
    }

    private function pusherConfig(): array
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

    private function getDonorPerfectConfig(): array
    {
        $fields = [];

        foreach (range(9, 22) as $index) {
            $key = "dp_meta_{$index}";
            $field = sys_get("dp_meta{$index}_field");

            $fields[$key] = [
                'key' => $key,
                'label' => null,
                'field' => null,
                'default' => null,
                'autocomplete' => (bool) sys_get("dp_meta{$index}_autocomplete"),
            ];

            if ($field === null || $field === '') {
                continue;
            }

            $fields[$key]['field'] = $field;
            $fields[$key]['label'] = sys_get("dp_meta{$index}_label");
            $fields[$key]['default'] = sys_get("dp_meta{$index}_default");
        }

        return [
            'enabled' => (bool) dpo_is_enabled(),
            'udfs' => array_values($fields),
        ];
    }
}
