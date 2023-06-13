<?php

namespace Ds\Domain\Settings\Integrations\Config;

class DonorPerfectIntegrationSettingsConfig extends AbstractIntegrationSettingsConfig
{
    public $id = 'donorperfect';

    public $name = 'DonorPerfect';

    public $category = 'CRM';

    public $config_url = '/jpanel/settings/dp';

    public $description = 'Automatically push donor and gift data in realtime.';

    public $help_url = 'https://help.givecloud.com/en/articles/2108929-donorperfect';

    public function isAvailable(): bool
    {
        return true;
    }

    public function isInstalled(): bool
    {
        return dpo_is_enabled();
    }

    public function userCanAdminister(): bool
    {
        return user()->can('admin.dpo');
    }
}
