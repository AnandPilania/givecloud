<?php

namespace Ds\Domain\Settings\Integrations\Config;

use Ds\Domain\Commerce\Models\PaymentProvider;

class PaySafeIntegrationSettingsConfig extends AbstractIntegrationSettingsConfig
{
    public $id = 'paysafe';

    public $name = 'PaySafe';

    public $category = 'Payments';

    public $config_url = '/jpanel/settings/payment/paysafe';

    public $description = 'Process credit and debit payments.';

    public $help_url = 'https://help.givecloud.com/en/articles/2628993-paysafe';

    public function isAvailable(): bool
    {
        return isGivecloudPro();
    }

    public function isInstalled(): bool
    {
        return PaymentProvider::provider('paysafe')->enabled()->count() > 0;
    }

    public function userCanAdminister(): bool
    {
        return true;
    }
}
