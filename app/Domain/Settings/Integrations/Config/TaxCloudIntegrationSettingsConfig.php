<?php

namespace Ds\Domain\Settings\Integrations\Config;

class TaxCloudIntegrationSettingsConfig extends AbstractIntegrationSettingsConfig
{
    public $id = 'taxcloud';

    public $name = 'TaxCloud';

    public $category = 'Accounting';

    public $config_url = '/jpanel/settings/taxcloud';

    public $description = 'Calculate and remit sales tax across the United States.';

    public $help_url = 'https://help.givecloud.com/en/articles/3081919-taxcloud';

    public function isAvailable(): bool
    {
        return isGivecloudPro();
    }

    public function isInstalled(): bool
    {
        return ! empty(sys_get('taxcloud_api_key'));
    }

    public function userCanAdminister(): bool
    {
        return true;
    }
}
