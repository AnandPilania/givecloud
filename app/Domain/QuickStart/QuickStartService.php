<?php

namespace Ds\Domain\QuickStart;

use Ds\Domain\MissionControl\MissionControlService;
use Ds\Domain\QuickStart\Tasks\AbstractTask;
use Ds\Domain\QuickStart\Tasks\BrandingSetup;
use Ds\Domain\QuickStart\Tasks\ChoosePlan;
use Ds\Domain\QuickStart\Tasks\CustomEmails;
use Ds\Domain\QuickStart\Tasks\CustomizeDonorPortal;
use Ds\Domain\QuickStart\Tasks\DonationItem;
use Ds\Domain\QuickStart\Tasks\DonorPerfectIntegration;
use Ds\Domain\QuickStart\Tasks\SetupLiveGateway;
use Ds\Domain\QuickStart\Tasks\TaxReceipts;
use Ds\Domain\QuickStart\Tasks\TaxReceiptTemplates;
use Ds\Domain\QuickStart\Tasks\TestTransactions;
use Ds\Domain\QuickStart\Tasks\TurnOnLiveGateway;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class QuickStartService
{
    public function tasks(): Collection
    {
        return collect([
            'setup' => [
                BrandingSetup::initialize(),
                DonationItem::initialize(),
                DonorPerfectIntegration::initialize(),
                TaxReceipts::initialize(),
                TaxReceiptTemplates::initialize(),
            ],
            'goingLive' => [
                TestTransactions::initialize(),
                SetupLiveGateway::initialize(),
                ChoosePlan::initialize(),
                TurnOnLiveGateway::initialize(),
            ],
            'next' => [
                CustomEmails::initialize(),
                CustomizeDonorPortal::initialize(),
                // ThankYouPages::initialize(),
            ],
        ]);
    }

    public function shouldShowExpandedChecklist(): bool
    {
        if (fromUtc(site()->created_at)->addDays(180)->isPast()) {
            return false;
        }

        return $this->tasks()
            ->flatten()
            ->filter(fn ($task) => $task->isActive())
            ->reject(fn ($task) => $task->isCompleted())
            ->isNotEmpty();
    }

    public function updateTaskStatus(AbstractTask $task): void
    {
        app(MissionControlService::class)->updateQuickStartTask($task->slug(), [
            'is_active' => $task->isActive(),
            'is_completed' => $task->isCompleted(),
            'is_skipped' => Arr::get($task->toArray(), 'isSkipped', false),
        ]);
    }

    public function toArray(): array
    {
        return $this->tasks()->map(function ($items) {
            return collect($items)->map(function (AbstractTask $task) {
                return $task->toArray();
            })->all();
        })->all();
    }

    public function toJson($options = 0): string
    {
        return json_encode($this->jsonSerialize(), JSON_THROW_ON_ERROR | $options);
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
