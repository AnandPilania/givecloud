<?php

namespace Ds\Domain\Settings\Integrations\Config;

class USPSIntegrationSettingsConfig extends AbstractIntegrationSettingsConfig
{
    public $id = 'usps';

    public $name = 'United States Postal Service';

    public $category = 'Fullfill & Ship';

    public $config_url = '/jpanel/settings/shipping';

    public $description = 'Show live shipping rates on your payment screens.';

    public $help_url = '';

    public function isAvailable(): bool
    {
        return isGivecloudPro();
    }

    public function isInstalled(): bool
    {
        return sys_get('shipping_usps_enabled');
    }

    public function userCanAdminister(): bool
    {
        return true;
    }
}
