<?php

namespace Ds\Console\Commands\DonorPerfect;

use Ds\Repositories\AccountRepository;
use Ds\Services\DonorPerfectService;
use Illuminate\Console\Command;
use Throwable;

class SyncMembershipsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dp:sync-memberships {--show-progress : Show progress bar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync DonorPerfect memberships.';

    /** @var \Ds\Illuminate\Console\ProgressBar */
    private $bar;

    /**
     * Execute the console command.
     *
     * @return int|void
     */
    public function handle(AccountRepository $accountRepository, DonorPerfectService $donorPerfectService)
    {
        if (! dpo_is_enabled()) {
            return 1;
        }

        if ($this->option('show-progress')) {
            $this->bar = $this->createProgressBar(
                $accountRepository->countAccountsWithDpMembershipStartingToday()
            );
        }

        $accountRepository->chunkAccountsWithDpMembershipStartingToday(250, function ($accounts) use ($donorPerfectService) {
            foreach ($accounts as $account) {
                optional($this->bar)->setMessage("syncing membership for supporter #{$account->id}");

                try {
                    $donorPerfectService->updateDonorMembership(
                        $account->donor_id,
                        $account->membership->pivot->groupAccountTimespan
                    );
                } catch (Throwable $e) {
                    report($e);
                }

                optional($this->bar)->advance();
            }
        });

        optional($this->bar)->finish();
        optional($this->bar)->newLine();
    }
}
