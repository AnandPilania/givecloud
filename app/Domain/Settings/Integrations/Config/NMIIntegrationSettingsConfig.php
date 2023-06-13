<?php

namespace Ds\Domain\Settings\Integrations\Config;

use Ds\Domain\Commerce\Models\PaymentProvider;

class NMIIntegrationSettingsConfig extends AbstractIntegrationSettingsConfig
{
    public $id = 'nmi';

    public $name = 'NMI';

    public $category = 'Payments';

    public $config_url = '/jpanel/settings/payment/nmi';

    public $description = 'Process credit and debit payments across 1000s of processors.';

    public $help_url = 'https://help.givecloud.com/en/articles/2631870-network-merchants-inc-nmi';

    public function isAvailable(): bool
    {
        return isGivecloudPro();
    }

    public function isInstalled(): bool
    {
        return PaymentProvider::provider('nmi')->enabled()->count() > 0;
    }

    public function userCanAdminister(): bool
    {
        return true;
    }
}
