<?php

namespace Ds\Domain\QuickStart\Tasks;

use Ds\Domain\Commerce\Models\PaymentProvider;

class SetupLiveGateway extends AbstractTask
{
    public function title(): string
    {
        return 'Add a Payment Gateway';
    }

    public function description(): string
    {
        return 'Choose from a list of payment gateways to easily collect contributions with Givecloud.';
    }

    public function action(): string
    {
        return route('backend.settings.payment');
    }

    public function actionText(): string
    {
        return 'Add Gateway';
    }

    public function knowledgeBase(): string
    {
        return 'https://help.givecloud.com/en/articles/4603859-connecting-your-payment-gateway';
    }

    public function isCompleted(): bool
    {
        return PaymentProvider::query()
            ->where('enabled', true)
            ->where('provider_type', '!=', 'test')
            ->where('provider_type', '!=', 'offline')
            ->get()
            ->filter(fn ($provider) => $provider->test_mode === false)
            ->isNotEmpty();
    }
}
