<?php

namespace Tests\Fakes\HotGlue;

use Ds\Domain\HotGlue\Listeners\AbstractHandler;
use Ds\Domain\HotGlue\Targets\AbstractTarget;
use Ds\Events\Event;

class FakeHandler extends AbstractHandler
{
    public function state(Event $event): array
    {
        return [
            'foo' => $event->foo,
        ];
    }

    public function target(): AbstractTarget
    {
        return new class extends AbstractTarget {
            public string $name = 'test-target';

            public function listens(): array
            {
                return [];
            }
        };
    }
}
