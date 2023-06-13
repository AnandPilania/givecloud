<?php

namespace Ds\Domain\Settings\Integrations\Config;

use Ds\Models\Hook;

class CustomWebhooksIntegrationSettingsConfig extends AbstractIntegrationSettingsConfig
{
    public $id = 'webhooks';

    public $name = 'Custom Webhooks';

    public $category = 'Custom Integrations';

    public $config_url = '/jpanel/settings/hooks';

    public $description = 'Develop a custom integration with Givecloud.';

    public $help_url = 'https://help.givecloud.com/en/articles/2108540-custom-webhooks';

    public function isAvailable(): bool
    {
        return true;
    }

    public function isInstalled(): bool
    {
        return Hook::active()->count() > 0;
    }

    public function userCanAdminister(): bool
    {
        return user()->can('hooks.edit');
    }
}
