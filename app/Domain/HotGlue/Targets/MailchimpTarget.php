<?php

namespace Ds\Domain\HotGlue\Targets;

use Ds\Domain\HotGlue\Listeners\Mailchimp\MemberOptinChanged;
use Ds\Events\MemberOptinChanged as MemberOptinChangedEvent;

class MailchimpTarget extends AbstractTarget
{
    public string $name = 'mailchimp';

    public function listens(): array
    {
        return [
            MemberOptinChangedEvent::class => [
                MemberOptinChanged::class,
            ],
        ];
    }
}
