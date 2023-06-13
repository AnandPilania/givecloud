<?php

namespace Ds\Domain\Settings\Integrations\Config;

class HubSpotHotGlueIntegrationSettingsConfig extends AbstractIntegrationSettingsConfig
{
    public $id = 'hubspot';

    public $name = 'HubSpot';

    public $category = 'CRM';

    public $config_url = '/jpanel/settings/hotglue/hubspot';

    public $description = 'Automatically push customer data to your Hubspot account in realtime.';

    public $help_url = ''; // TODO

    public function isAvailable(): bool
    {
        return feature('hotglue_hubspot');
    }

    public function isInstalled(): bool
    {
        return sys_get('hotglue_hubspot_linked');
    }

    public function userCanAdminister(): bool
    {
        return $this->isAvailable();
    }
}
