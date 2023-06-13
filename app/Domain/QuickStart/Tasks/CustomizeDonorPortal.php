<?php

namespace Ds\Domain\QuickStart\Tasks;

use Ds\Domain\QuickStart\Concerns\IsSkippable;

class CustomizeDonorPortal extends AbstractTask
{
    use IsSkippable;

    public function title(): string
    {
        return 'Customize the Donor Portal';
    }

    public function description(): string
    {
        return 'Save time and resources by creating a one-of-kind online experience for your supporters with the donor portal.';
    }

    public function action(): string
    {
        return route('backend.settings.supporters');
    }

    public function actionText(): string
    {
        return 'Customize Portal';
    }

    public function knowledgeBase(): string
    {
        return 'https://help.givecloud.com/en/articles/2761049-donor-portal#h_aa8b53cfc4';
    }

    public function isCompleted(): bool
    {
        // Looking at first setting that has not its default value
        // Sometime $value from prototype-site.sql and defaults.php is not the same...
        // The sql value is specified here.
        $settings = [
            'referral_sources_isactive' => '1',
            'referral_sources_other' => null,
            'referral_sources_options' => null,
            'donor_title' => null,
            'force_country' => null,
            'allow_account_types_on_web' => null,
            'nps_enabled' => '1',
            'marketing_optout_reason_required' => null,
        ];

        return collect($settings)
            ->filter(function ($value, string $setting) {
                if ($value === null) {
                    return sys_get($setting) === config('sys.defaults.' . $setting);
                }

                return sys_get($setting) === $value;
            })->count() < count($settings);
    }
}
