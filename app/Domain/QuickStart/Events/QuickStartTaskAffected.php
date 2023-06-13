<?php

namespace Ds\Domain\QuickStart\Events;

use Ds\Domain\QuickStart\Tasks\AbstractTask;
use Ds\Events\Event;
use Illuminate\Foundation\Events\Dispatchable;

class QuickStartTaskAffected extends Event
{
    use Dispatchable;

    public AbstractTask $task;

    public function __construct(AbstractTask $task)
    {
        $this->task = $task;
    }
}
