<?php

namespace Ds\Domain\Settings\Integrations\Config;

class SalesforceHotGlueIntegrationSettingsConfig extends AbstractIntegrationSettingsConfig
{
    public $id = 'salesforce';

    public $name = 'Salesforce';

    public $category = 'CRM';

    public $config_url = '/jpanel/settings/salesforce';

    public $description = 'Automatically push customer and contribution data in realtime.';

    public $help_url = 'https://help.givecloud.com/en/articles/5659398-salesforce';

    public function isAvailable(): bool
    {
        return feature('hotglue_salesforce');
    }

    public function isInstalled(): bool
    {
        return sys_get('bool:hotglue_salesforce_linked', false);
    }

    public function userCanAdminister(): bool
    {
        return $this->isAvailable();
    }
}
