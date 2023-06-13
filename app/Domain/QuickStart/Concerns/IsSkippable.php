<?php

namespace Ds\Domain\QuickStart\Concerns;

use Ds\Domain\QuickStart\Events\QuickStartTaskAffected;

trait IsSkippable
{
    public function isSkipped()
    {
        return sys_get('bool:' . $this->skipKey(), false);
    }

    public function skip(): bool
    {
        $result = sys_set($this->skipKey(), true);

        QuickStartTaskAffected::dispatch($this);

        return $result;
    }

    public function unskip(): bool
    {
        $result = sys_set($this->skipKey(), false);

        QuickStartTaskAffected::dispatch($this);

        return $result;
    }

    protected function skipKey(): string
    {
        return 'quickstart-' . $this->slug() . '_skipped';
    }
}
