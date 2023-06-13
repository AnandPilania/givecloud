<?php

namespace Ds\Domain\QuickStart\Tasks;

class BrandingSetup extends AbstractTask
{
    public function title(): string
    {
        return 'Set your Branding Colors, and Add a Logo';
    }

    public function description(): string
    {
        return 'Adding your branding will increase your organization\'s engagement, trust and create a sense of familiarity for existing supporters.';
    }

    public function action(): string
    {
        return route('backend.bucket.index');
    }

    public function actionText(): string
    {
        return 'Go to Branding';
    }

    public function knowledgeBase(): string
    {
        return 'https://help.givecloud.com/en/articles/3370571-how-to-design-your-website-the-basics';
    }

    public function isCompleted(): bool
    {
        return sys_get('default_logo') !== ''
            && sys_get('default_logo') !== 'https://givecloud.co/static/img/gc-logo.svg'
            && sys_get('default_logo') !== 'https://cdn.givecloud.co/static/etc/new-site-logo.svg';
    }
}
