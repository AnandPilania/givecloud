<?php

namespace Ds\Domain\Settings\Integrations\Config;

use Illuminate\Contracts\Routing\UrlGenerator;

abstract class AbstractIntegrationSettingsConfig
{
    /** @var bool */
    public $available = false;

    /** @var string */
    public $category = '';

    /** @var string */
    public $config_url = '';

    /** @var string */
    public $description = '';

    /** @var string */
    public $help_url = '';

    /** @var string */
    public $id = '';

    /** @var bool */
    public $installed = false;

    /** @var string */
    public $name = '';

    /** @var bool */
    public $user_can_administer = false;

    /** @var \Illuminate\Contracts\Routing\UrlGenerator */
    protected $url;

    public function __construct(UrlGenerator $url)
    {
        $this->url = $url;

        $this->available = $this->isAvailable();
        $this->installed = $this->isInstalled();
        $this->user_can_administer = $this->userCanAdminister();
    }

    abstract public function isAvailable(): bool;

    abstract public function isInstalled(): bool;

    abstract public function userCanAdminister(): bool;

    public function isDeprecated(): bool
    {
        return false;
    }

    public function getImageSrc(): string
    {
        $integrationImageFileName = "$this->id.png";
        $localIntegrationImagePath = "jpanel/assets/images/integrations/$integrationImageFileName";

        if (file_exists($localIntegrationImagePath)) {
            return $this->url->asset($localIntegrationImagePath);
        }

        return "https://cdn.givecloud.co/static/integrations/$integrationImageFileName";
    }
}
