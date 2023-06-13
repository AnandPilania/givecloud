<?php

namespace Ds\Domain\HotGlue\Listeners\HubSpot;

use Ds\Domain\HotGlue\Listeners\AbstractHandler;
use Ds\Domain\HotGlue\Targets\AbstractTarget;
use Ds\Domain\HotGlue\Targets\HubSpotTarget;
use Ds\Domain\HotGlue\Transformers\ContactTransformer;
use Ds\Events\Event;
use League\Fractal\Resource\Collection;

class AccountUpdated extends AbstractHandler
{
    public function target(): AbstractTarget
    {
        return app(HubSpotTarget::class);
    }

    public function state(Event $event): array
    {
        $state = new Collection([$event->account], new ContactTransformer, 'Contacts');

        return app('fractal')->createArray($state);
    }
}
