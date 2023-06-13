<?php

namespace Ds\Domain\Settings\Integrations\Config;

use Ds\Domain\Commerce\Models\PaymentProvider;

class VancoIntegrationSettingsConfig extends AbstractIntegrationSettingsConfig
{
    public $id = 'vanco';

    public $name = 'Vanco Payments';

    public $category = 'Payments';

    public $config_url = '/jpanel/settings/payment/vanco';

    public $description = 'Process credit and debit payments.';

    public $help_url = 'https://help.givecloud.com/en/articles/2629010-vanco';

    public function isAvailable(): bool
    {
        return isGivecloudPro();
    }

    public function isInstalled(): bool
    {
        return PaymentProvider::provider('vanco')->enabled()->count() > 0;
    }

    public function userCanAdminister(): bool
    {
        return true;
    }
}
