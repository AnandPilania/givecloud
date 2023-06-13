<?php

namespace Ds\Domain\Settings\Integrations\Config;

class ZapierIntegrationSettingsConfig extends AbstractIntegrationSettingsConfig
{
    public $id = 'zapier';

    public $name = 'Zapier';

    public $category = 'Custom Integrations';

    public $config_url = '/jpanel/settings/zapier';

    public $description = 'Connect your apps and automate workflows between them.';

    public $help_url = 'https://help.givecloud.com/en/articles/4586219-zapier-integration';

    public function isAvailable(): bool
    {
        return true;
    }

    public function isInstalled(): bool
    {
        return sys_get('zapier_enabled');
    }

    public function userCanAdminister(): bool
    {
        return true;
    }
}
