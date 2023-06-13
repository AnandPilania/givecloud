<?php

namespace Ds\Domain\Settings\Integrations\Config;

class ShipStationIntegrationSettingsConfig extends AbstractIntegrationSettingsConfig
{
    public $id = 'shipstation';

    public $name = 'ShipStation';

    public $category = 'Fullfill & Ship';

    public $config_url = '/jpanel/settings/shipstation';

    public $description = 'Send contribution data to ShipStation for fullfilling and shipping contributions.';

    public $help_url = 'https://help.givecloud.com/en/articles/2651943-shipstation';

    public function isAvailable(): bool
    {
        return isGivecloudPro();
    }

    public function isInstalled(): bool
    {
        return (bool) sys_get('shipstation_pass');
    }

    public function userCanAdminister(): bool
    {
        return true;
    }
}
