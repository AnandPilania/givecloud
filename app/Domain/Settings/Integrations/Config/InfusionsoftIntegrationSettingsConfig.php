<?php

namespace Ds\Domain\Settings\Integrations\Config;

class InfusionsoftIntegrationSettingsConfig extends AbstractIntegrationSettingsConfig
{
    public $id = 'infusionsoft';

    public $name = 'Infusionsoft';

    public $category = 'CRM';

    public $config_url = '/jpanel/settings/infusionsoft';

    public $description = 'Automatically push customer and contribution data in realtime.';

    public $help_url = 'https://help.givecloud.com/en/articles/2892885-infusionsoft';

    public function isAvailable(): bool
    {
        return true;
    }

    public function isInstalled(): bool
    {
        return ! empty(sys_get('infusionsoft_token'));
    }

    public function userCanAdminister(): bool
    {
        return user()->can('admin.infusionsoft');
    }
}
