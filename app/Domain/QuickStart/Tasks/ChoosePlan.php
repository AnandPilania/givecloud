<?php

namespace Ds\Domain\QuickStart\Tasks;

use Ds\Repositories\ChargebeeRepository;

class ChoosePlan extends AbstractTask
{
    public function title(): string
    {
        return 'Select a Pricing Plan';
    }

    public function description(): string
    {
        return 'Pick a plan option to maximize your fundraising experience best.';
    }

    public function action(): string
    {
        return route('backend.settings.billing');
    }

    public function actionText(): string
    {
        return 'Choose a Plan';
    }

    public function isActive(): bool
    {
        return site('direct_billing_enabled');
    }

    public function knowledgeBase(): string
    {
        // TODO
        return '';
    }

    public function isCompleted(): bool
    {
        return app(ChargebeeRepository::class)->getSubscription() !== null;
    }
}
