<?php

namespace Ds\Domain\Settings;

use Ds\Domain\Theming\Liquid\Filters\ColorFilters;

class GivecloudExpressConfigRepository
{
    public function getAvailableFeatures(): array
    {
        return [
            'accounts',
            'dcc_ai_plus',
            'embedded_donation_forms',
            'fundraising_forms',
            'fundraising_forms_peer_to_peer',
            'fundraising_forms_standard_layout',
            'hotglue_hubspot',
            'hotglue_mailchimp',
            'hotglue_salesforce',
            'metadata',
            'social_login',
            'supporter_search',
            'trackorder',
        ];
    }

    public function getAvailablePermissions(): array
    {
        return [
            'account',
            'account.edit',
            'admin',
            'admin.advanced',
            'admin.dpo',
            'admin.infusionsoft',
            'admin.general',
            'admin.payments',
            'admin.website',
            'dashboard',
            'dashboard.view',
            'file',
            'file.edit',
            'file.view',
            'hooks',
            'hooks.edit',
            'member',
            'member.add',
            'member.edit',
            'member.login',
            'member.merge',
            'member.view',
            'order',
            'order.edit',
            'order.refund',
            'order.view',
            'product',
            'product.add',
            'product.edit',
            'product.view',
            'recurringpaymentprofile',
            'recurringpaymentprofile.charge',
            'recurringpaymentprofile.edit',
            'recurringpaymentprofile.view',
            'reports.payments_details',
            'transaction',
            'transaction.refund',
            'transaction.view',
            'user',
            'user.add',
            'user.edit',
            'zapier',
        ];
    }

    public function getConfigOverrides(): array
    {
        return [
            'account_login_features' => 'view-profile,view-billing,edit-profile,edit-billing,view-orders,view-receipts,view-payment-methods,edit-payment-methods,delete-default-payment-method,view-subscriptions,end-subscriptions,edit-subscription-amount,edit-subscription-date',
            'allow_account_types_on_web' => '0',
            'dcc_stripe_application_fee_billing' => '1',
            'default_logo' => fn () => $this->getOrgLogo(),
            'default_color_1' => fn () => $this->getDefaultColour(),
            'default_color_2' => fn () => $this->getDefaultColour('color_darken', 10),
            'default_color_3' => fn () => $this->getDefaultColour('color_lighten', 10),
            'donor_title' => 'hidden',
            'enable_intercom' => 'all',
            'nps_enabled' => '0',
            'referral_sources_isactive' => '0',
            'use_fulfillment' => 'never',
        ];
    }

    private function getOrgLogo(): ?string
    {
        /** @var \Ds\Models\Media|string */
        $media = sys_get('org_logo');

        if (is_string($media)) {
            return $media ?: null;
        }

        return $media->thumbnail_url ?? null;
    }

    private function getDefaultColour(?string $methodName = null, float $amount = 0): ?string
    {
        $primaryColour = sys_get('org_primary_color');

        if (empty($primaryColour)) {
            return null;
        }

        if ($methodName && $amount) {
            return ColorFilters::{$methodName}($primaryColour, $amount) ?: null;
        }

        return $primaryColour;
    }
}
