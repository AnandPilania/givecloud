<?php

namespace Ds\Domain\QuickStart\Tasks;

use Ds\Models\Email;

class CustomEmails extends AbstractTask
{
    public function title(): string
    {
        return 'Personalize the Automated Emails';
    }

    public function description(): string
    {
        return 'Refine and modify a series of automated emails available on Givecloud to include branding and warm messaging for your supporters.';
    }

    public function action(): string
    {
        return route('backend.settings.email');
    }

    public function actionText(): string
    {
        return 'Setup Emails';
    }

    public function knowledgeBase(): string
    {
        return 'https://help.givecloud.com/en/articles/3367638-automated-email-communication';
    }

    public function isCompleted(): bool
    {
        return Email::query()
            ->active()
            ->whereNotNull('updated_by')
            ->where('updated_by', '!=', 1)
            ->get()
            ->isNotEmpty();
    }
}
