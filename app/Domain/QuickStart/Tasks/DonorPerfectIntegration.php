<?php

namespace Ds\Domain\QuickStart\Tasks;

use Ds\Domain\MissionControl\MissionControlService;
use Ds\Domain\Settings\Integrations\Config\DonorPerfectIntegrationSettingsConfig;

class DonorPerfectIntegration extends AbstractTask
{
    public function title(): string
    {
        return 'Connect the DonorPerfect Integration';
    }

    public function description(): string
    {
        return 'Unite the power of Givecloud with DonorPerfect to sync all contributions, transactions and supporter data automatically, making DonorPerfect your source of truth. ';
    }

    public function knowledgeBase(): string
    {
        return 'https://help.givecloud.com/en/articles/4555409-donorperfect-connecting-your-account-to-givecloud';
    }

    public function action(): string
    {
        return app(DonorPerfectIntegrationSettingsConfig::class)->config_url;
    }

    public function actionText(): string
    {
        return 'Connect DonorPerfect';
    }

    public function isActive(): bool
    {
        return app(MissionControlService::class)->getSite()->partner->identifier === 'dp';
    }

    public function isCompleted(): bool
    {
        return app(DonorPerfectIntegrationSettingsConfig::class)->isInstalled();
    }
}
