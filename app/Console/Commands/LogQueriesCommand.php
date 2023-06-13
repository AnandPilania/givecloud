<?php

namespace Ds\Console\Commands;

use Illuminate\Console\Command;

class LogQueriesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:log-queries {--disable} {ip_address?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enable query logging for requests from a specific IP.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $ip = $this->argument('ip_address');

        if (empty($ip) || $this->option('disable')) {
            sys_set('log_queries_for_ip', null);
        } else {
            sys_set('log_queries_for_ip', $ip);
        }
    }
}
