<?php

namespace Ds\Domain\HotGlue;

use Ds\Providers\DomainEventServiceProviderInterface;

class HotGlueEventServiceProvider implements DomainEventServiceProviderInterface
{
    public static function listens(): array
    {
        $listeners = collect(HotGlue::TARGETS)
            ->map(fn (string $target) => app($target)->listens())
            ->toArray();

        return array_merge_recursive(...$listeners);
    }
}
