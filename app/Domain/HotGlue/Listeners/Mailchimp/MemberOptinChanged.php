<?php

namespace Ds\Domain\HotGlue\Listeners\Mailchimp;

use Ds\Domain\HotGlue\Listeners\AbstractHandler;
use Ds\Domain\HotGlue\Targets\AbstractTarget;
use Ds\Domain\HotGlue\Targets\MailchimpTarget;
use Ds\Domain\HotGlue\Transformers\Mailchimp\AccountTransformer;
use Ds\Events\Event;
use League\Fractal\Resource\Collection;

class MemberOptinChanged extends AbstractHandler
{
    public function state(Event $event): array
    {
        $contact = new Collection([$event->member], new AccountTransformer, 'Customers');

        return app('fractal')->createArray($contact);
    }

    public function target(): AbstractTarget
    {
        return app(MailchimpTarget::class);
    }
}
