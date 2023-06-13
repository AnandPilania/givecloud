<?php

namespace Ds\Console\Commands;

use Ds\Models\GroupAccount;
use Ds\Models\GroupAccountTimespan;
use Illuminate\Console\Command;

class BackfillGroupAccountsAggregates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backfill:groupaccounts-aggregates {--missing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill Group Accounts Aggregates';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $pairs = GroupAccount::query()
            ->select(['account_id', 'group_id'])
            ->distinct()
            ->with(['account', 'group']);

        if ($this->option('missing')) {
            $pairs->whereNull('group_account_timespan_id');
        }

        $count = $pairs->count();

        if (empty($count)) {
            return;
        }

        $bar = $this->createProgressBar($count);

        $pairs->lazy()->each(function (GroupAccount $pair) use ($bar) {
            if (empty($pair->account) || empty($pair->group)) {
                $bar->error("Error: missing account ({$pair->account_id}) or group ({$pair->group_id}) detected");
                $bar->advance();

                return;
            }

            try {
                GroupAccountTimespan::aggregate($pair->group_id, $pair->account_id);
            } catch (\Throwable $e) {
                $bar->error($e->getMessage());
            }

            $bar->advance();
        });

        $bar->finish();
        $bar->newLine();
    }
}
