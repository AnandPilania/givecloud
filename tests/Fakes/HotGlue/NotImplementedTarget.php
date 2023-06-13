<?php

namespace Tests\Fakes\HotGlue;

use Ds\Domain\HotGlue\Targets\AbstractTarget;

class NotImplementedTarget extends AbstractTarget
{
    public string $name = 'not-implemented';

    public function listens(): array
    {
        return [];
    }
}
