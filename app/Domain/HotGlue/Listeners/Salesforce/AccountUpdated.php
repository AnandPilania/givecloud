<?php

namespace Ds\Domain\HotGlue\Listeners\Salesforce;

use Ds\Domain\HotGlue\Listeners\AbstractHandler;
use Ds\Domain\HotGlue\Targets\AbstractTarget;
use Ds\Domain\HotGlue\Targets\SalesforceTarget;
use Ds\Domain\HotGlue\Transformers\Salesforce\AccountTransformer;
use Ds\Events\Event;
use League\Fractal\Resource\Collection;

class AccountUpdated extends AbstractHandler
{
    public function shouldQueue(): bool
    {
        return sys_get('salesforce_contact_external_id')
            && parent::shouldQueue();
    }

    public function state(Event $event): array
    {
        $contact = new Collection([$event->account], new AccountTransformer, 'Contacts');

        return app('fractal')->createArray($contact);
    }

    public function target(): AbstractTarget
    {
        return app(SalesforceTarget::class);
    }
}
