<?php

namespace Ds\Domain\Settings\Integrations\Config;

class MailchimpHotGlueIntegrationSettingsConfig extends AbstractIntegrationSettingsConfig
{
    public $id = 'mailchimp';

    public $name = 'Mailchimp';

    public $category = 'Email Marketing';

    public $config_url = '/jpanel/settings/mailchimp';

    public $description = 'Automatically push customer data to your Mailchimp account in realtime.';

    public $help_url = 'https://help.givecloud.com/en/articles/6960156-mailchimp';

    public function isAvailable(): bool
    {
        return feature('hotglue_mailchimp');
    }

    public function isInstalled(): bool
    {
        return sys_get('bool:hotglue_mailchimp_linked', false);
    }

    public function userCanAdminister(): bool
    {
        return $this->isAvailable();
    }
}
