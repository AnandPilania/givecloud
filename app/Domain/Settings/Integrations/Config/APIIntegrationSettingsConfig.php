<?php

namespace Ds\Domain\Settings\Integrations\Config;

class APIIntegrationSettingsConfig extends AbstractIntegrationSettingsConfig
{
    public $id = 'api';

    public $name = 'API';

    public $category = 'Custom Integrations';

    public $config_url = '/jpanel/profile';

    public $description = 'Push and pull data from your Givecloud system using APIs.';

    public $help_url = 'https://help.givecloud.com/en/articles/5211700-developer-apis';

    public function isAvailable(): bool
    {
        return true;
    }

    public function isInstalled(): bool
    {
        return true;
    }

    public function userCanAdminister(): bool
    {
        return false;
    }
}
