<?php

namespace Ds\Domain\QuickStart\Tasks;

use Ds\Models\Payment;

class TestTransactions extends AbstractTask
{
    public function title(): string
    {
        return 'Test your Donation Form';
    }

    public function description(): string
    {
        return 'Refine your Supporter Experience by running a test transaction.';
    }

    public function action(): string
    {
        return '';
    }

    public function actionText(): string
    {
        return '';
    }

    public function knowledgeBase(): string
    {
        return 'https://help.givecloud.com/en/articles/4603888-test-credit-cards-ach-accounts';
    }

    public function isCompleted(): bool
    {
        return Payment::query()->where('livemode', false)->succeededOrPending()->exists();
    }
}
