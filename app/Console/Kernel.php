<?php

namespace Ds\Console;

use Ds\Illuminate\Console\Application as Artisan;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application when in APP_LEVEL.
     *
     * @var array
     */
    protected $appLevelCommands = [
        Commands\PostReleaseNotesCommand::class,
    ];

    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\AuthorizeNet\ReconciliationCommand::class,
        Commands\BackfillChargebeePreferredPaymentCurrency::class,
        Commands\BackfillContributionsCommand::class,
        Commands\BackfillLedgerEntries::class,
        Commands\BackfillPaymentsCommand::class,
        Commands\BackfillGroupAccountsAggregates::class,
        Commands\CleanupOrderTableCommand::class,
        Commands\DepleteLedgerEntriesForDeletedOrders::class,
        Commands\DonorPerfect\SyncMembershipsCommand::class,
        Commands\LogQueriesCommand::class,
        Commands\NMI\AccountUpdaterCommand::class,
        Commands\NMI\ReconciliationCommand::class,
        Commands\NMI\SetupLinkCommand::class,
        Commands\NotificationsCommand::class,
        Commands\PayPal\ReconciliationCommand::class,
        Commands\Paysafe\ReconciliationCommand::class,
        Commands\PhpCommand::class,
        Commands\RecurringAccountCommand::class,
        Commands\RecurringBatchCommand::class,
        Commands\SendDigestsCommand::class,
        Commands\SetupInitialPassportClientsCommand::class,
        \Ds\Domain\Messenger\Console\ProvisionPhoneNumberCommand::class,
        \Ds\Domain\Messenger\Console\ReleasePhoneNumberCommand::class,
        \Ds\Illuminate\Database\Console\MigrateFreshCommand::class,
        \Ds\Domain\Salesforce\Console\Commands\Backfill::class,
        \Ds\Domain\Salesforce\Console\Commands\BackfillSupporters::class,
        \Ds\Domain\Salesforce\Console\Commands\BackfillContributions::class,
        \Ds\Domain\Salesforce\Console\Commands\BackfillContributionPayments::class,
        \Ds\Domain\Salesforce\Console\Commands\BackfillLineItems::class,
        \Ds\Domain\Salesforce\Console\Commands\BackfillDiscounts::class,
        \Ds\Domain\Salesforce\Console\Commands\BackfillPayments::class,
        \Ds\Domain\Salesforce\Console\Commands\BackfillTransactions::class,
        \Ds\Domain\Salesforce\Console\Commands\BackfillTransactionsLineItems::class,
        \Ds\Domain\QuickStart\Console\BackfillCommand::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        // $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

    /**
     * Get the Artisan application instance.
     *
     * @return \Illuminate\Console\Application
     */
    protected function getArtisan()
    {
        if (defined('APP_LEVEL_ENABLED')) {
            $this->commands = $this->appLevelCommands;
        }

        if (is_null($this->artisan)) {
            $this->artisan = (new Artisan($this->app, $this->events, $this->app->version()))
                ->resolveCommands($this->commands);
        }

        return $this->artisan;
    }
}
