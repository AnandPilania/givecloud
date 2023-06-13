<?php

namespace Ds\Domain\Settings\Integrations;

use Ds\Domain\Settings\Integrations\Config\APIIntegrationSettingsConfig;
use Ds\Domain\Settings\Integrations\Config\AuthorizeDotNetIntegrationSettingsConfig;
use Ds\Domain\Settings\Integrations\Config\CanadaPostIntegrationSettingsConfig;
use Ds\Domain\Settings\Integrations\Config\CaymanGatewayIntegrationSettingsConfig;
use Ds\Domain\Settings\Integrations\Config\CustomWebhooksIntegrationSettingsConfig;
use Ds\Domain\Settings\Integrations\Config\DonorPerfectIntegrationSettingsConfig;
use Ds\Domain\Settings\Integrations\Config\DoubleTheDonationIntegrationSettingsConfig;
use Ds\Domain\Settings\Integrations\Config\FedExIntegrationSettingsConfig;
use Ds\Domain\Settings\Integrations\Config\GoCardlessIntegrationSettingsConfig;
use Ds\Domain\Settings\Integrations\Config\HubSpotHotGlueIntegrationSettingsConfig;
use Ds\Domain\Settings\Integrations\Config\InfusionsoftIntegrationSettingsConfig;
use Ds\Domain\Settings\Integrations\Config\MailchimpHotGlueIntegrationSettingsConfig;
use Ds\Domain\Settings\Integrations\Config\NMIIntegrationSettingsConfig;
use Ds\Domain\Settings\Integrations\Config\PayPalIntegrationSettingsConfig;
use Ds\Domain\Settings\Integrations\Config\PaySafeIntegrationSettingsConfig;
use Ds\Domain\Settings\Integrations\Config\SafeSaveIntegrationSettingsConfig;
use Ds\Domain\Settings\Integrations\Config\SalesforceHotGlueIntegrationSettingsConfig;
use Ds\Domain\Settings\Integrations\Config\SalesforceIntegrationSettingsConfig;
use Ds\Domain\Settings\Integrations\Config\ShipStationIntegrationSettingsConfig;
use Ds\Domain\Settings\Integrations\Config\StripeIntegrationSettingsConfig;
use Ds\Domain\Settings\Integrations\Config\TaxCloudIntegrationSettingsConfig;
use Ds\Domain\Settings\Integrations\Config\UPSIntegrationSettingsConfig;
use Ds\Domain\Settings\Integrations\Config\USPSIntegrationSettingsConfig;
use Ds\Domain\Settings\Integrations\Config\VancoIntegrationSettingsConfig;
use Ds\Domain\Settings\Integrations\Config\ZapierIntegrationSettingsConfig;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Support\Collection;

class IntegrationSettingsService
{
    public $integrationSettingsConfigs = [
        DonorPerfectIntegrationSettingsConfig::class,
        InfusionsoftIntegrationSettingsConfig::class,
        DoubleTheDonationIntegrationSettingsConfig::class,
        ShipStationIntegrationSettingsConfig::class,
        CanadaPostIntegrationSettingsConfig::class,
        FedExIntegrationSettingsConfig::class,
        USPSIntegrationSettingsConfig::class,
        UPSIntegrationSettingsConfig::class,
        TaxCloudIntegrationSettingsConfig::class,
        CustomWebhooksIntegrationSettingsConfig::class,
        AuthorizeDotNetIntegrationSettingsConfig::class,
        StripeIntegrationSettingsConfig::class,
        PaySafeIntegrationSettingsConfig::class,
        PayPalIntegrationSettingsConfig::class,
        GoCardlessIntegrationSettingsConfig::class,
        SafeSaveIntegrationSettingsConfig::class,
        NMIIntegrationSettingsConfig::class,
        VancoIntegrationSettingsConfig::class,
        CaymanGatewayIntegrationSettingsConfig::class,
        ZapierIntegrationSettingsConfig::class,
        APIIntegrationSettingsConfig::class,
        SalesforceIntegrationSettingsConfig::class,
        SalesforceHotGlueIntegrationSettingsConfig::class,
        MailchimpHotGlueIntegrationSettingsConfig::class,
        HubSpotHotGlueIntegrationSettingsConfig::class,
    ];

    /** @var \Illuminate\Contracts\Routing\UrlGenerator */
    protected $url;

    public function __construct(UrlGenerator $url)
    {
        $this->url = $url;
    }

    public function getAll(): Collection
    {
        return (new Collection($this->integrationSettingsConfigs))
            ->map(function ($integrationsettingsConfig) {
                return new $integrationsettingsConfig($this->url);
            })->filter(fn ($integration) => $integration->available);
    }

    public function getInstalledAndAdministrable(): Collection
    {
        return $this->getAll()->filter(function ($integration) {
            return $integration->installed && $integration->user_can_administer;
        });
    }
}
