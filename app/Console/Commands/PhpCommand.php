<?php

namespace Ds\Console\Commands;

use Illuminate\Console\Command;
use Throwable;

class PhpCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'php {--force} {filename}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run a PHP script in the context of a site.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        if (! $this->option('force')) {
            if (! $this->confirm('Just what do you think you are doing, Dave?')) {
                $this->comment('Command Cancelled!');

                return false;
            }
        }

        $filename = $this->argument('filename');

        if (! file_exists($filename)) {
            $this->error("'$filename': No such file");

            return;
        }

        try {
            require $filename;
        } catch (Throwable $e) {
            $this->error($e);
            $this->error($e->getTraceAsString());
        }
    }
}
