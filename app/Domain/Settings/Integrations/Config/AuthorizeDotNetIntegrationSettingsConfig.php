<?php

namespace Ds\Domain\Settings\Integrations\Config;

use Ds\Domain\Commerce\Models\PaymentProvider;

class AuthorizeDotNetIntegrationSettingsConfig extends AbstractIntegrationSettingsConfig
{
    public $id = 'authorize';

    public $name = 'Authorize.net';

    public $category = 'Payments';

    public $config_url = '/jpanel/settings/payment/authorizenet';

    public $description = 'Process credit and debit payments.';

    public $help_url = 'https://help.givecloud.com/en/articles/2629021-authorize-net';

    public function isAvailable(): bool
    {
        return isGivecloudPro();
    }

    public function isInstalled(): bool
    {
        return PaymentProvider::provider('authorizenet')->enabled()->count() > 0;
    }

    public function userCanAdminister(): bool
    {
        return true;
    }
}
