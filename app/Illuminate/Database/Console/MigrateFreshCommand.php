<?php

namespace Ds\Illuminate\Database\Console;

use Ds\Illuminate\Database\MySqlSnapshot;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Console\Migrations\FreshCommand;
use Illuminate\Database\Events\DatabaseRefreshed;

class MigrateFreshCommand extends FreshCommand
{
    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (! $this->confirmToProceed()) {
            return 1;
        }

        $database = $this->input->getOption('database');

        $this->call('db:wipe', array_filter([
            '--database' => $database,
            '--drop-views' => $this->option('drop-views'),
            '--drop-types' => $this->option('drop-types'),
            '--force' => true,
        ]));

        if (sys_get()->inTestingEnvironment()) {
            $this->info('Loading snapshot.');

            MySqlSnapshot::load('prototype-site');
        }

        $this->call('migrate', array_filter([
            '--database' => $database,
            '--path' => $this->input->getOption('path'),
            '--realpath' => $this->input->getOption('realpath'),
            '--schema-path' => $this->input->getOption('schema-path'),
            '--force' => true,
            '--step' => $this->option('step'),
        ]));

        if ($this->laravel->bound(Dispatcher::class)) {
            $this->laravel[Dispatcher::class]->dispatch(
                new DatabaseRefreshed
            );
        }

        if ($this->needsSeeding()) {
            $this->runSeeder($database);
        }

        return 0;
    }
}
