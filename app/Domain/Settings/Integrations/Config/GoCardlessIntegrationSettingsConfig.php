<?php

namespace Ds\Domain\Settings\Integrations\Config;

use Ds\Domain\Commerce\Models\PaymentProvider;

class GoCardlessIntegrationSettingsConfig extends AbstractIntegrationSettingsConfig
{
    public $id = 'gocardless';

    public $name = 'GoCardless';

    public $category = 'Payments';

    public $config_url = '/jpanel/settings/payment/gocardless';

    public $description = 'Process direct debit payments globally.';

    public $help_url = 'https://help.givecloud.com/en/articles/1541668-gocardless';

    public function isAvailable(): bool
    {
        return isGivecloudPro();
    }

    public function isInstalled(): bool
    {
        return PaymentProvider::provider('gocardless')->enabled()->count() > 0;
    }

    public function userCanAdminister(): bool
    {
        return true;
    }
}
