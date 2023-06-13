<?php

namespace Ds\Console\Commands;

use Ds\Common\ProcessManager;
use Ds\Models\Member as Account;
use Ds\Models\RecurringBatch;
use Illuminate\Console\Command;

class RecurringBatchCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recurring:batch
                            {--dry-run : Print summary of potential batches}
                            {--show-progress : Show progress bar}
                            {--threshold=500 : The threshold required to utilize parallel processing}
                            {--max-simultaneous=4 : The maximum number of simultaneous processes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Charge recurring payment profiles in batches.';

    /** @var \Ds\Illuminate\Console\ProgressBar */
    private $bar;

    /** @var \Ds\Models\RecurringBatch */
    private $batch;

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $count = Account::has('chargeableRpps')->count();

        if ($count === 0) {
            return;
        }

        $dryRun = $this->option('dry-run');
        $threshold = $this->option('threshold');

        // if there are more than `threshold` supporters to process then use
        // parallel processing to process `max-simultaneous` supporters at a time
        $maxSimultaneous = $count > $threshold ? $this->option('max-simultaneous') : 1;

        if ($dryRun) {
            $this->comment('<fg=yellow;options=bold>Process (' . $count . ') supporters with chargeable profiles</>');
            $this->comment('|' . str_repeat('-', 105));
            $this->comment(sprintf(
                '| %6s  %32s  %18s  %5s  %2s  %11s  %s',
                'ID',
                'NAME',
                'ACCT TYPE',
                'LAST4',
                '#',
                'AMT',
                'NEXT BILL'
            ));
            $this->comment('|' . str_repeat('-', 105));
        } else {
            $this->batch = RecurringBatch::start($count, $maxSimultaneous);

            if ($this->option('show-progress')) {
                $this->bar = $this->createProgressBar($count);
            }
        }

        $query = Account::withCount('chargeableRpps')
            ->having('chargeable_rpps_count', '>', 0)
            ->orderBy('chargeable_rpps_count', 'desc');

        $processManager = new ProcessManager;
        $processManager->setMaxSimultaneous($maxSimultaneous);

        foreach ($query->cursor() as $account) {
            optional($this->bar)->setMessage("processing supporter #{$account->id}");

            $processManager->runArtisanCommand(
                'recurring:account',
                ['account_id' => $account->getKey(), '--batch-id' => optional($this->batch)->getKey(), '--dry-run' => $dryRun],
                function ($output) {
                    ($this->bar ?? $this)->comment(trim($output));

                    optional($this->batch)->accountProcessed();
                }
            );

            optional($this->bar)->advance();
        }

        $processManager->wait();

        optional($this->batch)->finish();
        optional($this->batch)->sendSummaryToAccountAdmins();

        optional($this->bar)->finish();
        optional($this->bar)->newLine();
    }
}
