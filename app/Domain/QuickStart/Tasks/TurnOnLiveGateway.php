<?php

namespace Ds\Domain\QuickStart\Tasks;

use Ds\Domain\Commerce\Models\PaymentProvider;

class TurnOnLiveGateway extends AbstractTask
{
    public function title(): string
    {
        return 'Turn on your Payment Gateway';
    }

    public function description(): string
    {
        return 'Enable your payment gateway to collect monetary contributions from your supporters.';
    }

    public function action(): string
    {
        return route('backend.settings.payment');
    }

    public function actionText(): string
    {
        return 'Turn on Live Gateway';
    }

    public function knowledgeBase(): string
    {
        return 'https://help.givecloud.com/en/articles/4603859-connecting-your-payment-gateway';
    }

    public function isCompleted(): bool
    {
        return ($provider = PaymentProvider::getCreditCardProvider(false)) !== null
            && $provider->test_mode === false;
    }
}
