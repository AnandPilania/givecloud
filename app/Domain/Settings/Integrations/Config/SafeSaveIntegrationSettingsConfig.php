<?php

namespace Ds\Domain\Settings\Integrations\Config;

use Ds\Domain\Commerce\Models\PaymentProvider;

class SafeSaveIntegrationSettingsConfig extends AbstractIntegrationSettingsConfig
{
    public $id = 'safesave';

    public $name = 'SafeSave';

    public $category = 'Payments';

    public $config_url = '/jpanel/settings/payment/safesave';

    public $description = 'Process credit and debit payments.';

    public $help_url = 'https://help.givecloud.com/en/articles/2629001-safesave';

    public function isAvailable(): bool
    {
        return isGivecloudPro();
    }

    public function isInstalled(): bool
    {
        return PaymentProvider::provider('safesave')->enabled()->count() > 0;
    }

    public function userCanAdminister(): bool
    {
        return true;
    }
}
