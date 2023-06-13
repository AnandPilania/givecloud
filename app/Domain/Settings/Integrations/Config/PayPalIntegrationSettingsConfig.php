<?php

namespace Ds\Domain\Settings\Integrations\Config;

use Ds\Domain\Commerce\Models\PaymentProvider;

class PayPalIntegrationSettingsConfig extends AbstractIntegrationSettingsConfig
{
    public $id = 'paypal';

    public $name = 'PayPal Express Checkout';

    public $category = 'Payments';

    public $config_url = '/jpanel/settings/payment/paypalexpress';

    public $description = 'Increase conversion by allowing donors to choose PayPal.';

    public $help_url = 'https://help.givecloud.com/en/articles/1541667-paypal';

    public function isAvailable(): bool
    {
        return true;
    }

    public function isInstalled(): bool
    {
        return PaymentProvider::provider('paypalexpress')->enabled()->count() > 0;
    }

    public function userCanAdminister(): bool
    {
        return true;
    }
}
