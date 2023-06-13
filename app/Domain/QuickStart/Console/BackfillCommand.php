<?php

namespace Ds\Domain\QuickStart\Console;

use Ds\Domain\QuickStart\Events\QuickStartTaskAffected;
use Ds\Domain\QuickStart\QuickStartService;
use Ds\Domain\QuickStart\Tasks\AbstractTask;
use Illuminate\Console\Command;

class BackfillCommand extends Command
{
    protected $signature = 'quickstart:backfill';

    protected $description = 'Backfills quickstart statuses';

    public function handle()
    {
        app(QuickStartService::class)->tasks()
            ->flatten()
            ->each(function (AbstractTask $task) {
                $this->info('Backfilling task ' . class_basename($task));
                QuickStartTaskAffected::dispatch($task);
            });

        return 0;
    }
}
