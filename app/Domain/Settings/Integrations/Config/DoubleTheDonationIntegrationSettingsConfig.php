<?php

namespace Ds\Domain\Settings\Integrations\Config;

class DoubleTheDonationIntegrationSettingsConfig extends AbstractIntegrationSettingsConfig
{
    public $id = 'doublethedonation';

    public $name = 'Double The Donation';

    public $category = 'Fundraising';

    public $config_url = '/jpanel/settings/double-the-donation';

    public $description = 'Present employer matching technology on your thank you pages.';

    public $help_url = 'https://help.givecloud.com/en/articles/1541669-double-the-donation';

    public function isAvailable(): bool
    {
        return sys_get('bool:feature_double_the_donation', false);
    }

    public function isInstalled(): bool
    {
        return sys_get('bool:double_the_donation_enabled', false);
    }

    public function userCanAdminister(): bool
    {
        return $this->isAvailable();
    }
}
