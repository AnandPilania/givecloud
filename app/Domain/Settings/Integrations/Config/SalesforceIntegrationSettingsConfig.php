<?php

namespace Ds\Domain\Settings\Integrations\Config;

class SalesforceIntegrationSettingsConfig extends AbstractIntegrationSettingsConfig
{
    public $id = 'salesforce';

    public $name = 'Salesforce';

    public $category = 'CRM';

    public $config_url = '/jpanel/settings/salesforce-legacy';

    public $description = 'Automatically push customer and contribution data in realtime.';

    public $help_url = 'https://help.givecloud.com/en/articles/5659398-salesforce';

    public function isAvailable(): bool
    {
        // Only available if previously installed.
        return $this->isInstalled();
    }

    public function isDeprecated(): bool
    {
        return true;
    }

    public function isInstalled(): bool
    {
        return sys_get('bool:salesforce_enabled', false);
    }

    public function userCanAdminister(): bool
    {
        return $this->isAvailable();
    }
}
