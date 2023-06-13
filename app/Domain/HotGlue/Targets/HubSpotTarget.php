<?php

namespace Ds\Domain\HotGlue\Targets;

use Ds\Domain\HotGlue\Listeners\HubSpot\AccountUpdated;
use Ds\Domain\HotGlue\Listeners\HubSpot\OrderPaid;
use Ds\Events\AccountCreated;
use Ds\Events\AccountWasUpdated;
use Ds\Events\OrderWasCompleted;
use Ds\Events\OrderWasRefunded;

class HubSpotTarget extends AbstractTarget
{
    public string $name = 'hubspot';

    public function listens(): array
    {
        return [
            AccountCreated::class => [
                AccountUpdated::class,
            ],
            AccountWasUpdated::class => [
                AccountUpdated::class,
            ],
            OrderWasCompleted::class => [
                OrderPaid::class,
            ],
            OrderWasRefunded::class => [
                OrderPaid::class,
            ],
        ];
    }
}
