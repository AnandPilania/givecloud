<?php

namespace Ds\Domain\QuickStart\Listeners;

use Ds\Domain\QuickStart\Events\QuickStartTaskAffected;
use Ds\Domain\QuickStart\QuickStartService;
use Illuminate\Contracts\Queue\ShouldQueue;

class QuickStartTaskAffectedListener implements ShouldQueue
{
    public function handle(QuickStartTaskAffected $event): void
    {
        app(QuickStartService::class)->updateTaskStatus($event->task);
    }
}
