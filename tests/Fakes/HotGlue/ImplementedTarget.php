<?php

namespace Tests\Fakes\HotGlue;

use Ds\Domain\HotGlue\Targets\AbstractTarget;

class ImplementedTarget extends AbstractTarget
{
    public string $name = 'implemented';

    public function listens(): array
    {
        return [];
    }
}
