<?php

namespace Ds\Console\Commands;

use Carbon\Carbon;
use Ds\Mail\ContributionsDailyDigest;
use Ds\Models\User;
use Illuminate\Console\Command;

class SendDigestsCommand extends Command
{
    /** @var string */
    protected $signature = 'send-digests {date?}';

    /** @var string */
    protected $description = 'Send digests to users.';

    public function handle(): void
    {
        if ($date = $this->argument('date')) {
            Carbon::setTestNow(fromLocal($date));
        }

        $this->sendDailyDigests();

        if ($date) {
            Carbon::setTestNow();
        }
    }

    private function sendDailyDigests(): void
    {
        $users = User::query()
            ->notSuperUser()
            ->where('notify_digest_daily', true)
            ->lazy();

        foreach ($users as $user) {
            $user->mail(new ContributionsDailyDigest(today()->asDate(), $user));
        }
    }
}
