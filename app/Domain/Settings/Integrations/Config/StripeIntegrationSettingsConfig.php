<?php

namespace Ds\Domain\Settings\Integrations\Config;

use Ds\Domain\Commerce\Models\PaymentProvider;

class StripeIntegrationSettingsConfig extends AbstractIntegrationSettingsConfig
{
    public $id = 'stripe';

    public $name = 'Stripe';

    public $category = 'Payments';

    public $config_url = '/jpanel/settings/payment/stripe';

    public $description = 'Process credit and debit payments.';

    public $help_url = 'https://help.givecloud.com/en/articles/2629026-stripe';

    public function isAvailable(): bool
    {
        return true;
    }

    public function isInstalled(): bool
    {
        return PaymentProvider::provider('stripe')->enabled()->count() > 0;
    }

    public function userCanAdminister(): bool
    {
        return true;
    }
}
