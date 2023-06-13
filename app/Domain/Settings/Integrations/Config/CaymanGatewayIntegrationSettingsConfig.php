<?php

namespace Ds\Domain\Settings\Integrations\Config;

use Ds\Domain\Commerce\Models\PaymentProvider;

class CaymanGatewayIntegrationSettingsConfig extends AbstractIntegrationSettingsConfig
{
    public $id = 'caymangateway';

    public $name = 'Cayman Gateway';

    public $category = 'Payments';

    public $config_url = '/jpanel/settings/payment/caymangateway';

    public $description = 'Process credit and debit payments in the Carribean and South America.';

    public $help_url = 'https://help.givecloud.com/en/articles/2765408-cayman-gateway';

    public function isAvailable(): bool
    {
        return isGivecloudPro();
    }

    public function isInstalled(): bool
    {
        return PaymentProvider::provider('caymangateway')->enabled()->count() > 0;
    }

    public function userCanAdminister(): bool
    {
        return true;
    }
}
